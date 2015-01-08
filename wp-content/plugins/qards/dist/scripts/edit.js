(function () {
	angular
		.module('dmApp', ['angularFileUpload', 'minicolors', 'ui.slider', 'ngSanitize'])
		.config(['$interpolateProvider',
			function($interpolateProvider) {
				$interpolateProvider.startSymbol('[[');
				$interpolateProvider.endSymbol(']]');
			}
		])
		.config(function (minicolorsProvider) {
			angular.extend(minicolorsProvider.defaults, {
				position: 'bottom right',
				animationSpeed: 0,
				hide: function() {
					$(this).removeClass('show');
				},
				hideSpeed: 0,
				show: function() {
					var dmControls = $(this).closest('.dm-controls'),
						minicolors = $(this).closest('.minicolors'),
						dmControlsHeight = dmControls.height(),
						documentHeight = $(document).height(),
						minicolorPanelHeight = 228,
						additionalSpace = 14;

					if(dmControls.offset().top + dmControlsHeight + minicolorPanelHeight + additionalSpace > documentHeight) {
						minicolors
							.removeClass('minicolor-additional-position-bottom')
							.addClass('minicolor-additional-position-top');
					} else {
						minicolors
							.removeClass('minicolor-additional-position-top')
							.addClass('minicolor-additional-position-bottom');
					}

					dmControls.find('.ui-slider').removeClass('show');
					$(this).addClass('show');
				},
				showSpeed: 0
			});
		});

	/**
	 * Delete last whitespace symbols
	 * @returns {*|string}
	 */
	String.prototype.trimEnd = function() { return this.replace(/\s+$/, ''); };

	/**
	 * Delete first whitespace symbols
	 * @returns {*|string}
	 */
	String.prototype.trimStart = function() { return this.replace(/^\s+/, ''); };
})();

(function () {
	angular
		.module('dmApp')
		.factory('layoutModel', ['$http', function($http) {
			var data = {},
				model = {};

			model.addTemplate = function(template) {
				data[template.id] = template.data;
			};

			model.removeTemplate = function(id) {
				delete data[id];
			};

			model.findById = function(id) {
				return data[id];
			};

			model.save = function() {
				return $http({
					url: ajaxurl,
					method: "POST",
					params: {
						action: 'dm_api',
						method: 'component.model.save'
					},
					data: {
						params: {
							models: data
						}
					}
				})
			};

			return model;

		}]);
})();

(function () {
	angular
		.module('dmApp')
		.controller('DMCtrl', DMCtrl);

	DMCtrl.$inject = ['$scope', '$rootScope', 'layoutModel', 'fonts'];
	function DMCtrl($scope, $rootScope, layoutModel, fontsService) {
		$scope.save = function() {
			layoutModel.save().success(function() {
				$scope.$broadcast('scope-was-saved');
				for(var i in $scope.arrayChanges) {
					$scope.arrayChanges[i].changed = false;
				}
			});
		};
		$scope.arrayChanges = [];
		$scope.shownCustomizer = [];

		$scope.$on('scope-was-loaded', function(event, data) {
			var templates = angular.element('[component-id]', '#templates'),
				templatesArray = [],
				position = 0;
			angular.forEach(templates, function(value, key){
				templatesArray.push(angular.element(value).attr('component-id'));
			});
			for(var i = 0; i < templatesArray.length; i++) {
				if(templatesArray[i] == data.id) {
					position = i;
					break;
				}
			}
			$scope.arrayChanges.splice(position, 0, data);
		});

		$scope.reorderBlocks = function() {
			var templates = angular.element('[component-id]', '#templates'),
				templatesArray = [];

			angular.forEach(templates, function(value, key){
				templatesArray.push(angular.element(value).attr('component-id'));
			});

			function sortFunction(a, b) {
				var indexA = templatesArray.indexOf(a['id']);
				var indexB = templatesArray.indexOf(b['id']);
				if(indexA < indexB) {
					return -1;
				}else if(indexA > indexB) {
					return 1;
				}else{
					return 0;
				}
			}
			$scope.arrayChanges.sort(sortFunction);
		};

		$scope.$on('scope-was-changed', function(event, data) {
			for(var i = 0; i < $scope.arrayChanges.length; i++) {
				if($scope.arrayChanges[i].id === data.id) {
					$scope.arrayChanges[i].changed = data.changed;
				}
			}
		});

		$scope.$on('scope-was-removed', function(event, data) {
			for(var i = 0; i < $scope.arrayChanges.length; i++) {
				if($scope.arrayChanges[i].id === data) {
					$scope.arrayChanges.splice(i, 1);
				}
			}
		});

		$scope.$on('add-customizer', function(event, data) {
			$scope.shownCustomizer.push(data);
			$scope.$apply();
		});

		$scope.$on('remove-customizer', function(event, data) {
			$scope.shownCustomizer.pop();
			$scope.$apply();
		});

		$scope.$watchCollection('shownCustomizer', function(newNames, oldNames) {
			if (newNames.length) {
				angular.element('[data-customizer-serial=' + newNames[newNames.length-1] + ']').css({
					'top': 430,
					'left': 1960
				}).show().addClass('in');
			}

			if(oldNames.length) {
				angular.element('[data-customizer-serial=' + oldNames[oldNames.length-1] + ']').removeClass('in').hide();
			}
		});

		$scope.keyDown = function (event) {
			if (event.which === 27) {
				$rootScope.$emit('text-editing-hide');
			}
		};

		$scope.click = function (event) {
			var parent = $('[text-popover]').parent();
			if (parent.find($(event.target)).length === 0 &&
				($(event.target).closest('[contenteditable]').length === 0 ||
				$(event.target).closest('[data-customizer]').length !== 0)) {
				$rootScope.$emit('text-editing-hide');
				$rootScope.$emit('text-editing-update');
			}
		};

		$scope.mouseUp = function (event) {
			$rootScope.$emit('text-editing-mouse-up');
		};

		$(window).on('resize', function () {
			$scope.$apply(function () {
				$rootScope.$emit('text-editing-hide');
			});
		});
	}
})();

(function () {
	angular
		.module('dmApp')
		.controller('FileUploadCtrl', ['$scope', '$element', '$upload',
			function($scope, $element, $upload) {
				$scope.onFileSelect = function($files, imageWidth) {
					//$files: an array of files selected, each file has name, size, and type.
					var count = 0;
					$scope.dmStoreMediaData.dataFiles = [];

					for (var i = 0; i < $files.length; i++) {
						var file = $files[i];
						$scope.upload = $upload.upload({
							url: pluginUrl + '/async-upload.php', //upload.php script, node.js route, or servlet url
							// method: 'POST',
							// headers: {'header-key': 'header-value'},
							// withCredentials: true,
							data: {
								max_width: imageWidth
							},
							file: file, // or list of files: $files for html5 only
							/* set the file formData name ('Content-Desposition'). Default is 'file' */
							//fileFormDataName: myFile, //or a list of names for multiple files (html5).
							/* customize how data is added to formData. See #40#issuecomment-28612000 for sample code */
							//formDataAppender: function(formData, key, val){}
						}).progress(function(evt) {
							//console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
						}).success(function(data, status, headers, config) {
							// file is uploaded successfully
							var extArr = config.file.type.split('/'),
								format = extArr[extArr.length - 1];

							if(data.result) {
								$scope.dmStoreMediaData.dataFiles.push({
									dataUrl: data.result,
									format: format
								});

								count++;

								if(count === $files.length) {
									$scope.dmStoreMediaData.uploaded = true;
								}
							} else if(data.error) {
								alert("Error occurred: (" + data.error.code + ") " + data.error.message);
							} else {
								alert("Upload error");
							}
						}).error(function(data, status, headers, config) {
							alert("Error occurred: " + (status == 413 ? "Your HTTP server does not allow to upload large files." : "HTTP Error number" + status + "."));
						});
						//.error(...)
						//.then(success, error, progress);
						//.xhr(function(xhr){xhr.upload.addEventListener(...)})// access and attach any event listener to XMLHttpRequest.
					}
					/* alternative way of uploading, send the file binary with the file's content-type.
					 Could be used to upload files to CouchDB, imgur, etc... html5 FileReader is needed.
					 It could also be used to monitor the progress of a normal http post/put request with large data*/
					// $scope.upload = $upload.http({...})  see 88#issuecomment-31366487 for sample code.
				};
			}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmUploadMedia', ['$compile', 'widgetIcons', function($compile, widgetIcons) {
			return {
				scope: {
					dmUploadMedia: '=',
					dmUploadType: '@'
				},
				link: link,
				controller: controller
			};

			function link($scope, $element, $attrs) {
				if($scope.dmUploadType === 'image' || $scope.dmUploadType === 'masonry') {
					var imageWidth = $attrs.width*2 || 1200
				}
				var uploadFile = '',
					typeFile = '<input class="dm-custom-file" type="file" ng-file-select="onFileSelect($files, ' + imageWidth + ')" accept="image/jpeg, image/pjpeg, image/png, image/x-png, image/gif, image/svg+xml, video/mp4, video/ogg, video/webm" /><span class="dm-custom-file-cover"></span>';

				if($attrs['dmMultipleUpload'] !== undefined) {
					typeFile = '<input class="dm-custom-file" type="file" ng-file-select="onFileSelect($files, ' + imageWidth + ')" multiple accept="image/jpeg, image/pjpeg, image/png, image/x-png, image/gif, image/svg+xml, video/mp4, video/ogg, video/webm" /><span class="dm-custom-file-cover"></span>';
				}

				uploadFile = '<div class="dm-upload-media ' + (($scope.dmUploadType === 'video') ? 'video' : '') + '" ng-controller="FileUploadCtrl">' +
					'<i class="dm-widget-icon">' +
					widgetIcons.getIcon(($scope.dmUploadType === 'video') ? 'video' : 'image') +
					'</i>' + typeFile +
					'</div>';

				$element.prepend($compile(uploadFile)($scope));

				$element.find('.dm-custom-file-cover')
					.on('mouseenter', function() {
						angular.element(this).parent().addClass('hover');
					})
					.on('mouseleave', function() {
						angular.element(this).parent().removeClass('hover');
					})
					.on('click', function() {
						$element.find('input').click();
					});
			}

			function controller ($scope) {
				$scope.dmStoreMediaData = {
					uploaded: false,
					dataFiles: []
				};
				$scope.$watch('dmStoreMediaData.uploaded', function() {
					if($scope.dmStoreMediaData.uploaded) {
						var k = 0;
						if($scope.dmUploadType === 'video') {
							for(k = 0; k < $scope.dmStoreMediaData.dataFiles.length; k++) {
								if($scope.dmStoreMediaData.dataFiles[k].format in $scope.dmUploadMedia.types) {
									$scope.dmUploadMedia.types[$scope.dmStoreMediaData.dataFiles[k].format] = $scope.dmStoreMediaData.dataFiles[k].dataUrl
								}
							}
							angularStartupKit.attachBgVideo('bgVideo', $scope.dmUploadMedia);
						} else if($scope.dmUploadType === 'masonry') {
							for(k = 0; k < $scope.dmStoreMediaData.dataFiles.length; k++) {
								$scope.dmUploadMedia.push({
									"image": $scope.dmStoreMediaData.dataFiles[k].dataUrl,
									"title": "Title",
									"uploaded": true
								})
							}
						} else {
							$scope.dmUploadMedia = $scope.dmStoreMediaData.dataFiles[0].dataUrl;
						}
						$scope.dmStoreMediaData.uploaded = false;
					}
				});
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.factory('fontsFactory', ['$http', function($http) {
			return {
				getFonts: function() {
					return $http({
						url: ajaxurl,
						method: "GET",
						params: {
							action: 'dm_api',
							method: 'font.get'
						},
						data: {
							params: {
							}
						}
					});
				}
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.factory('toggleWidget', [function() {
			var hide = function() {
				var allNonCustomizerWidgets = angular.element('.dm-remove-template-holder, .dm-carousel'),
					controlsViewBtns = angular.element('.dm-controlsView-btns');

				allNonCustomizerWidgets.each(function() {
					angular.element(this).removeClass('hide-widget');
				});
//				controlsViewBtns.addClass('show');
			};

			var show = function() {
				var allNonCustomizerWidgets = angular.element('.dm-remove-template-holder, .dm-carousel'),
					controlsViewBtns = angular.element('.dm-controlsView-btns');

				allNonCustomizerWidgets.each(function() {
					angular.element(this).addClass('hide-widget');
				});
				controlsViewBtns.removeClass('show');
			};

			return {
				hide: hide,
				show: show
			};
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.factory('URLValidator', [function() {
			var init = function(url) {
				return url ? true : false;
				//return (/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/).test(url);
			};

			return {
				init: init
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.factory('widgetIcons', [function() {
			var iconsArray = [{
				name: 'image',
				code: '<svg ng-non-bindable xmlns="http://www.w3.org/2000/svg" class="i-image" viewBox="0 0 20 16" enable-background="new 0 0 20 16" width="20px" height="16px" style="fill: #fff;">' +
					'<path d="M18 0h-16c-1.104 0-2 .896-2 2v12c0 1.105.896 2 2 2h16c1.105 0 2-.895 2-2v-12c0-1.104-.895-2-2-2zm-2.648 2.675c.985 0 1.784.744 1.784 1.663s-.799 1.662-1.784 1.662-1.784-.744-1.784-1.663.798-1.662 1.784-1.662zm-12.495 10.658l4.286-6.667 3.586 5.578 2.843-4.244 3.571 5.333h-14.286z" fill-rule="evenodd" clip-rule="evenodd"/>' +
					'</svg>'
			}, {
				name: 'url',
				code: '<svg ng-non-bindable width="16px" height="16px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
						'<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
							'<g id="Text-Editor" sketch:type="MSArtboardGroup" transform="translate(-170.000000, -54.000000)" fill="#FFFFFF">' +
								'<g id="Text-Bar--normal" sketch:type="MSLayerGroup" transform="translate(33.000000, 39.000000)">' +
									'<g id="Add-Link" transform="translate(137.000000, 15.000000)" sketch:type="MSShapeGroup">' +
										'<path d="M12.121,6.111 L6.111,12.121 L7.878,13.889 L13.889,7.878 L12.121,6.111" id="Fill-1"></path>' +
										'<path d="M14.464,11.282 L16.061,9.685 C17.645,8.101 17.645,5.524 16.061,3.94 C15.292,3.172 14.272,2.75 13.188,2.75 C12.103,2.75 11.082,3.172 10.315,3.94 L8.718,5.537 L6.951,3.769 L8.547,2.172 C9.787,0.933 11.435,0.25 13.188,0.25 C14.94,0.25 16.588,0.933 17.828,2.172 C20.386,4.731 20.386,8.894 17.828,11.452 L16.231,13.05 L14.464,11.282 Z M6.812,19.75 C5.059,19.75 3.411,19.067 2.172,17.828 C0.935,16.591 0.253,14.942 0.253,13.188 C0.253,11.433 0.935,9.785 2.172,8.547 L3.769,6.951 L5.537,8.718 L3.94,10.315 C2.356,11.899 2.356,14.477 3.94,16.061 C4.673,16.793 5.72,17.213 6.812,17.213 C7.905,17.213 8.952,16.793 9.685,16.061 L11.282,14.464 L13.05,16.231 L11.453,17.828 C10.214,19.067 8.566,19.75 6.812,19.75 Z" id="Fill-2"></path>' +
									'</g>' +
								'</g>' +
							'</g>' +
						'</g>' +
					'</svg>'
			}, {
				name: 'trash',
				code: '<svg ng-non-bindable xmlns="http://www.w3.org/2000/svg" class="i-trash" width="16px" height="16px" style="fill: #fff;" viewBox="0 0 17 16" enable-background="new 0 0 17 16">' +
                    '<path d="M-351.721-116.505h-11.558c-.435 0-.765.345-.731.767l.67 9.972c.033.422.417.766.853.766h9.974c.436 0 .819-.345.853-.766l.671-9.972c.032-.422-.297-.767-.732-.767zm-7.279 8.374c0 .483-.392.875-.875.875s-.875-.392-.875-.875v-5.505c0-.483.392-.875.875-.875s.875.392.875.875v5.505zm4.688.035c0 .464-.376.841-.841.841h-.006c-.464 0-.841-.376-.841-.841v-5.574c0-.464.376-.841.841-.841h.006c.464 0 .841.376.841.841v5.574zm4.312-11.904h-4c0-.552-.448-1-1-1h-5c-.552 0-1 .448-1 1h-4c-.552 0-1 .448-1 1s.448 1 1 1h15c.552 0 1-.448 1-1s-.448-1-1-1z" fill="none"/>' +
                    '<path d="M14.279 4.495h-11.558c-.436 0-.765.345-.731.767l.67 9.972c.034.421.418.766.853.766h9.974c.436 0 .819-.345.853-.766l.671-9.972c.032-.422-.297-.767-.732-.767zm-7.279 8.374c0 .483-.392.875-.875.875s-.875-.392-.875-.875v-5.505c0-.483.392-.875.875-.875s.875.392.875.875v5.505zm4.688.035c0 .464-.376.841-.841.841h-.006c-.464 0-.841-.377-.841-.841v-5.574c0-.464.376-.841.841-.841h.006c.464 0 .841.376.841.841v5.574zm4.312-11.904h-4c0-.552-.448-1-1-1h-5c-.552 0-1 .448-1 1h-4c-.552 0-1 .448-1 1s.448 1 1 1h15c.552 0 1-.448 1-1s-.448-1-1-1z" fill-rule="evenodd" clip-rule="evenodd"/>' +
                  '</svg>'
			}, {
				name: 'prev',
				code: '<svg ng-non-bindable width="8px" height="15px" viewBox="0 0 11 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
					'<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
					'<g id="Slides-2" sketch:type="MSArtboardGroup" transform="translate(-48.000000, -52.000000)" stroke="#FFFFFF" stroke-width="2">' +
					'<path d="M47,51 L54.875,60 L47,69" id="Prev-Slide-2" sketch:type="MSShapeGroup" transform="translate(52.500000, 61.000000) rotate(-180.000000) translate(-52.500000, -61.000000) "></path>' +
					'</g>' +
					'</g>' +
					'</svg>'
			}, {
				name: 'next',
				code: '<svg ng-non-bindable width="8px" height="15px" viewBox="0 0 11 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
					'<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
					'<g id="Slides-2" sketch:type="MSArtboardGroup" transform="translate(-150.000000, -50.000000)" stroke="#FFFFFF" stroke-width="2">' +
					'<path d="M151,51 L158.875,60 L151,69" id="Prev-Slide-2" sketch:type="MSShapeGroup"></path>' +
					'</g>' +
					'</g>' +
					'</svg>'
			}, {
				name: 'add',
				code: '<svg ng-non-bindable width="16px" height="16px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
					'<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
					'<g id="Slides-2" sketch:type="MSArtboardGroup" transform="translate(-93.000000, -135.000000)" stroke="#FFFFFF" stroke-width="2">' +
					'<g id="Add-Slide" sketch:type="MSLayerGroup" transform="translate(94.000000, 136.000000)">' +
					'<circle id="Oval-15" sketch:type="MSShapeGroup" cx="10" cy="10" r="10"></circle>' +
					'<path d="M5.5,10 L14.5553856,10" id="Line-2" stroke-linecap="square" sketch:type="MSShapeGroup"></path>' +
					'<path d="M10,5 L10,14.0553856" id="Line-3" stroke-linecap="square" sketch:type="MSShapeGroup"></path>' +
					'</g>' +
					'</g>' +
					'</g>' +
					'</svg>'
			}, {
				name: 'remove',
				code: '<svg ng-non-bindable width="16px" height="16px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
					'<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
					'<g id="Slides-2" sketch:type="MSArtboardGroup" transform="translate(-222.000000, -135.000000)" stroke="#FFFFFF" stroke-width="2">' +
					'<g id="Remove-Slide" sketch:type="MSLayerGroup" transform="translate(223.000000, 136.000000)">' +
					'<circle id="Oval-15" sketch:type="MSShapeGroup" cx="10" cy="10" r="10"></circle>' +
					'<path d="M5.5,10 L14.5553856,10" id="Line-2" stroke-linecap="square" sketch:type="MSShapeGroup"></path>' +
					'</g>' +
					'</g>' +
					'</g>' +
					'</svg>'
			}, {
				name: 'video',
				code: '<svg ng-non-bindable xmlns="http://www.w3.org/2000/svg" style="fill: #fff;" width="22px" height="13px" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 22 16" style="enable-background:new 0 0 22 16;" xml:space="preserve">' +
					'<g>' +
					'<g>' +
					'<path d="M13.087,2.673c-3.777-0.92-8.38-0.92-12.157,0c-0.523,0.155-0.929,0.76-0.929,1.306v8.011    c0,0.545,0.406,1.151,0.929,1.306c3.777,0.92,8.38,0.92,12.157,0c0.523-0.155,0.929-0.76,0.929-1.306V3.979    C14.016,3.433,13.61,2.828,13.087,2.673z M21.129,2.088l-4.203,2.399c-0.478,0.273-0.87,0.947-0.87,1.498v4.001    c0,0.551,0.391,1.225,0.87,1.498l4.203,2.399c0.478,0.273,0.869,0.046,0.869-0.505V2.593C21.998,2.042,21.607,1.815,21.129,2.088z    "/>' +
					'</g>' +
					'</g>' +
					'</svg>'
			}, {
				name: 'text',
				code: '<svg ng-non-bindable width="22px" height="22px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
					'<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
					'<g id="Menu" sketch:type="MSArtboardGroup" transform="translate(-47.000000, -79.000000)" fill="#FFFFFF">' +
					'<path d="M52.3058015,96.4036305 L64.9357044,83.7737275 L64.9357044,83.7737275 L62.212337,81.0503601 L49.5927616,93.6905905 L49.5941394,93.6919684 L52.3058015,96.4036305 Z M51.6629772,97.0464548 L50.6363636,98.0730683 C50.6363636,98.0730683 47,99.0133806 47,99.0133806 L47.9090909,95.3770169 L48.9504629,94.3339405 L48.9513151,94.3347927 L51.6629772,97.0464548 Z M65.5785288,83.1309032 L66.9843893,81.7250426 L64.2593467,79 L62.8546357,80.4070101 L65.5785288,83.1309032 L65.5785288,83.1309032 Z" id="Edit-2" sketch:type="MSShapeGroup"></path>' +
					'</g>' +
					'</g>' +
					'</svg>'
			}, {
				name: 'new-window',
				code: '<svg ng-non-bindable xmlns="http://www.w3.org/2000/svg" style="fill: #fff;" viewBox="0 0 15 16" enable-background="new 0 0 15 16" width="15px" height="15px">' +
					'<path d="M13.5 11h-8c-.827 0-1.5-.673-1.5-1.5v-8c0-.827.673-1.5 1.5-1.5h8c.827 0 1.5.673 1.5 1.5v8c0 .827-.673 1.5-1.5 1.5zm-7.5-2h7v-7h-7v7zM9.5 15h-8c-.827 0-1.5-.673-1.5-1.5v-8c0-.827.673-1.5 1.5-1.5h1.5v2h-1v7h7v-1h2v1.5c0 .827-.673 1.5-1.5 1.5z"/>' +
					'</svg>'
			}];
			var getIcon = function(name) {
				for(var i = 0; i < iconsArray.length; i++) {
					if(iconsArray[i].name === name) {
						return iconsArray[i].code;
					}
				}
				return '';
			};

			return {
				getIcon: getIcon
			};
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('customizer', ['$compile', 'toggleWidget', function($compile, toggleWidget) {
			return {
				restrict: "E",
				replace: true,
				transclude: true,
				template: '<div class="dm-controls" data-html2canvas-ignore>' +
					'<div class="dm-popover-top-space"></div>' +
					'<div class="arrow"></div>' +
					'<div class="btn-group" ng-transclude></div>' +
					'<div class="dm-popover-bottom-space"></div>' +
					'</div>',
				link: link
			};

			function link(scope, element) {
				var serial = Math.floor(Math.random() * 10000000000000000),
					customizerParent = element.parent(),
					template = element.closest('.dm-template'),
					templates = angular.element('#templates'),
					popoverSpace = 5;

				customizerParent.attr('data-customizer', serial);
				element.attr('data-customizer-serial', serial);
				template.append(element);

				customizerParent.on('click', function(e) {
					var idElement = angular.element(this).attr('data-customizer'),
						allCustomizer = angular.element('[data-customizer-serial], [data-popover-serial]'),
						leftPosition = customizerParent.offset().left - template.offset().left,
						popoverWidth = element.outerWidth(),
						templateWidth = template.width(),
						item = angular.element('[data-customizer-serial="' + idElement + '"]'),
						arrow = item.find('.arrow'),
						topPosition = customizerParent.offset().top - element.height() - template.offset().top - 12;

			        $('body').removeClass('preview');
			        $('#sideMenu').addClass('noHover');
			        $('#subMenu').addClass('invisible');

					if ( !item.hasClass('in') ) {
						if ( !customizerParent.hasClass('button') ) {
							window.getSelection().removeAllRanges();
						}
						allCustomizer.each(function() {
							var _this = angular.element(this);
							if(_this.is(':visible')){
								_this.scope().$broadcast('init-customizer');
							}
							_this.removeClass('in').removeClass('focus').removeClass('toRight').hide();
						});

						if ( template.offset().top - templates.offset().top + topPosition < 12 ) {
							item.removeClass('top').addClass('bottom');
							topPosition = customizerParent.offset().top - template.offset().top + customizerParent.outerHeight();
						} else {
							item.removeClass('bottom').addClass('top');
						}

						toggleWidget.show();

						if ( item.hasClass('dm-external-nav') ) {
							if(!item.hasClass('toRight'))
								leftPosition += customizerParent.outerWidth() / 2 - item.outerWidth() / 2;
						}

						if ( item.hasClass('dm-popover-image') ) {
							topPosition = customizerParent.find('img').position().top + 20;
							leftPosition = customizerParent.find('img').position().left + 20;
						}

						item.css({
							'top': topPosition,
							'left': leftPosition,
							'right': 'auto'
						}).show().addClass('in');

						arrow.css({
				        	'left': popoverWidth / 2
				        });

						if ( item.hasClass('dm-popover-image') && !item.closest('.dm-template').find('.dm-settingsBlock-holder').hasClass('visible') ) {
							item.closest('.dm-template').find('.dm-settingsBlock-holder').addClass('visible');
						}
						else if ( item.hasClass('dm-popover-image') && item.closest('.dm-template').find('.dm-settingsBlock-holder').hasClass('visible') ) {
							item.closest('.dm-template').find('.dm-settingsBlock-holder').removeClass('visible').removeClass('show');
							item.removeClass('in');
						}
						else {
							item.closest('.dm-template').find('.dm-settingsBlock-holder').removeClass('visible').removeClass('show');
						}
						item.closest('.dm-template').find('.dm-controlsView-btns').removeClass('show');
					}
					else {
						var idElement = angular.element(this).attr('data-customizer'),
							el = angular.element('[data-customizer-serial="' + idElement + '"]');

						if(!el.hasClass('focus')) {
							toggleWidget.hide();
							el.removeClass('in').hide();
						}

						item.closest('.dm-template').find('.dm-settingsBlock-holder').removeClass('visible').removeClass('show');

						setTimeout(function() {
							if ( customizerParent.hasClass('custom-img') && !el.hasClass('in') ) {
								customizerParent.removeClass('hover');
							}
						},100);
					}
					item.on('click', function(e) {
						e.stopPropagation();
					});
					$('body').on('click', function() {
						item.removeClass('in').hide();
					});
					return false;
				});
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmTemplate', [function() {
			return {
				scope: true,
				controller: controller
			};

			function controller($scope, $element, $attrs, templateModel, layoutModel, $http) {
				var initScope;

				$scope.id = $attrs.componentId;
				$scope.$emit('scope-was-loaded', {
					id: $scope.id,
					changed: false
				});

				$http(
					getReguest('component.model.get', {
						component_id: $scope.id
					})
				).then(function(data) {
					populateData(data.data.result);
					invokeExtInit($attrs.dmTemplate, $attrs.componentId);
					initScope = angular.copy($scope.data);
				});

				$scope.removeTemplate = function() {
					$scope.$emit('scope-was-removed', $scope.id);
					layoutModel.removeTemplate($scope.id);
					$scope.$destroy();
				};

				$scope.findById = function() {
					return layoutModel.findById($scope.id);
				};

				$scope.$watch('data', function(newNames, oldNames) {
					if(newNames && oldNames) {
						if(angular.equals(initScope, newNames)) {
							$scope.$emit('scope-was-changed', {
								id: $scope.id,
								changed: false
							});
						} else {
							$scope.$emit('scope-was-changed', {
								id: $scope.id,
								changed: true
							});
						}
					}
				}, true);

				$scope.$on('scope-was-saved', function(event, args) {
					initScope = angular.copy($scope.data);
				});

				function getReguest(methodName, paramsName) {
					return {
						url: ajaxurl,
						method: "POST",
						params: {
							action: 'dm_api',
							method: methodName
						},
						data: {
							params: paramsName
						}
					}
				}

				function populateData(data) {
					layoutModel.addTemplate(templateModel.create($scope.id, data));
					$scope.data = layoutModel.findById($scope.id);
				}

				function invokeExtInit(id, uniqId) {
					var array = id.split('.'),
						parentName = array[0],
						componentId = array[1];
					
					var extModule;
					if(parentName === 'custom') {
						var subArray = componentId.split('_'),
							subParentName = subArray[0],
							subComponentId = subArray[1];
						extModule = subParentName;
					} else {
						extModule = parentName;
					}

					var scrUrl = scriptsUrls[extModule];
					window.loadExternalScript($('[component-id=' + uniqId + ']'), scrUrl, true);
				}
			}
		}]);
})();

//code
$(document).ready(function(e) {

    //SETTINGS
    var originalLayout = [],
        menuOpened;

    var draggableParams = {
        connectToSortable: "#templates",
        addClasses: false,
        helper: "clone",
        appendTo: 'body',
        distance: 50,
        drag: function(event, ui) {
            setTimeout(function() {
                $('div.placeholder').attr('style', 'height: 100px');
            }, 50);
            $(window).mousemove(function(event) {
                var windowY = event.pageY - $(window).scrollTop();
                var windowX = event.pageX;
                $('.ui-draggable-dragging').css('top', $(window).scrollTop() + windowY - 50).css('left', windowX - 50).css('width', '100px!important');
            });
        }
    };

    //Make Blocks Sotrable
    var sortableParams = {
        opacity: 0.75,
        placeholder: "placeholder",
        revert: 300,
        distance: 10,
        items: '.dm-template:not(.dm-menu)',
        handle: '.fake-draggable',
        refreshPositions: true,
        start: function(event, ui) {
            $('.placeholder').height(ui.item.context.clientHeight);
        },
        receive: function(event, ui) {
            $('[data-component-id=' + ui.item.data('component-id') + '], [data-name]', '#templates').hide();
            generateTemplate(ui.item.data('name'), 'draggable');
            window.templateScreenshot = ui.item[0].childNodes[1].src;
        }
    };
    
    var blocks = [{
        "type": "cover",
        "count": "11",
        "name": "Cover"
    }, {
        "type": "image",
        "count": "7",
        "name": "Image"
    }, {
        "type": "text",
        "count": "7",
        "name": "Text"
    }, {
        "type": "feature",
        "count": "12",
        "name": "Feature"
    }, {
        "type": "grid",
        "count": "6",
        "name": "Grid"
    }, {
        "type": "menu",
        "count": "7",
        "name": "Menu"
    }, {
        "type": "footer",
        "count": "6",
        "name": "Footer"
    }, {
        "type": "subscribe",
        "count": "2",
        "name": "Subscribe"
    }];

    function mainMenuInit() {
        $('#sideMenu > ul').menuAim({
            activate: function(event) {
                if (!$('#sideMenu').hasClass('disabled')) {
                    $("#subMenu").removeClass('invisible');
                    $("#sideMenu div.selected, #sideMenu li.selected").removeClass('selected');
                    $(event).addClass('selected');
                    var currentItem = $(event).data('menu-item');
                    $('#subMenu').scrollTop(0);
                    $('#subMenu > div.visible').removeClass('visible');
                    $('#subMenu > div#' + currentItem).addClass('visible');
                }
            },
            exitMenu: function() {
                return true;
            }
        });
        $('img').on('dragstart', function(event) {
            event.preventDefault();
        });
        $("#subMenu > div").find('div').draggable(draggableParams).off('click').on('click', function() {
            if ( !isAdding ) {
                isAdding = true;
                generateTemplate($(this).data('name'));
                window.templateScreenshot = $(this).find('img').attr('src');
            }
        });
        $('#templates').droppable({
            drop: function(event, ui) {
                ui.draggable.addClass('draggable-invisible');
            }
        });
        $("#templates").sortable(sortableParams);
    }

    //getComponentId();
    setTimeout(function() {
        mainMenuInit();
    }, 0);

    //Create Submenu From Array 
    var blocksRows = [];
    blocks.forEach(function(element) {
        $('#subMenu').append('<div id="'+element.type+'"></div>');
        for (i = 0; i < element.count; i++) {
            var elNum = i + 1;
            blocksRows.push('<div data-name="'+element.type+'.'+element.type+elNum+'"><span>'+element.type+' #'+elNum+'</span><img src="' + pluginUrl +'img/generator/' + element.type + '-' + elNum + '.jpg"></div>');
        }
        $('#subMenu div#'+element.type).append(blocksRows.join('\n\n'));
        blocksRows = [];
    });

    //Menu Here
    $("#menu").on("mouseleave", function() {
        $("#subMenu").addClass('invisible');
    });

    //Toggle sidebar
    $('.dm-toggle').click(function() {
        $('body').toggleClass('preview');
        $("#subMenu").addClass('invisible');
        $('#sideMenu li.selected').removeClass('selected');
        setTimeout(function() {
            $('#sideMenu').toggleClass('noHover');
        },330);
    });

    function generateTemplate(templateId, type, isReset, elPos) {
        var templateBlocks = $('.dm-template', '#templates'),
            componentIds = [];

        $.each(templateBlocks, function() {
            componentIds.push(+$(this).attr('component-id'));
        });

        apiCall(
            'component.create',
            {
                template_id: templateId,
                component_ids: componentIds
            },
            function(result) {
                var componentId = result.component_id;

                if(type === 'draggable') {
                    addTemplate('draggable', result, isReset, elPos, templateId);
                } else {
                    addTemplate('non-draggable', result, isReset, elPos, templateId);
                }
            }
        );
    }

    function addTemplate(type, result, isReset, elPos, templateId) {
        var componentId = result.component_id,
            templates = $('#templates'),
            templateName = templateId.split('.')[0],
            htmlTemplate = result.html,
            cssTemplate = result.layout_css,
            componentIds = '',
            scrollTop = '',
            originalItem = '';

        $('#subMenu').addClass('invisible');
        $('body').removeClass('preview');
        $('#sideMenu').addClass('noHover');

        if (templates.children().length > 0) {
            if(templateName !== 'menu') {
                if ( type != 'draggable' ) {
                    templates.append('<div class="fake-template"><img src="' + templateScreenshot + '"></div>');
                    var lastTemplate = templates.children().last().prev();
                    scrollTop = lastTemplate.offset().top + lastTemplate.outerHeight() - 132;
                    if ( !isReset ) {
                        $('html, body').animate({scrollTop: scrollTop}, 750);
                    }
                }
                else {
                    var originalItem = '';
                    if ( !isReset ) {
                        originalItem = $('[data-component-id=' + componentId + '], [data-name]', '#templates');
                    }
                    else if ( isReset && elPos ) {
                        originalItem = elPos;
                    }
                    originalItem.after('<div class="fake-template"><img src="' + templateScreenshot + '"></div>');
                    scrollTop = $('.fake-template').offset().top - 132;
                    if ( !isReset ) {
                        $('html, body').animate({scrollTop: scrollTop}, 750);
                    }
                }
            } else {
                templates.prepend('<div class="fake-template"><img src="' + templateScreenshot + '"></div>');
                if ( !isReset ) {
                    $('html, body').animate({scrollTop: 0}, 750);
                }
            }
        }
        else {
            templates.append('<div class="fake-template"><img src="' + templateScreenshot + '"></div>');
        }

        if(templateName !== 'menu') {
            if (type === 'draggable') {
                if ( !isReset ) {
                    originalItem = $('[data-component-id=' + componentId + '], [data-name]', '#templates');
                }
                else if ( isReset && elPos ) {
                    originalItem = elPos;
                }
                $(htmlTemplate).insertAfter($('.fake-template'));
                if ( !isReset ) {
                    originalItem.remove();
                }
            } else {
                $('#templates').append(htmlTemplate);
            }
        } else {
            var template = $('.dm-menu', '#templates');
            if(template.length) {
                template.scope().removeTemplate();
                template.remove();
            }
            if ( !isReset ) {
                originalItem = $('[data-component-id=' + componentId + '], [data-name]', '#templates');
            }
            $('.fake-template').after(htmlTemplate);
            if ( !isReset && originalItem.length ) {
                originalItem.remove();
            }
        }

        var addedBlock = $('[component-id=' + componentId + ']', '#templates');
        addedBlock.addClass('tempHidden');

        if($('#initCSSFile').length) {
            $('#initCSSFile').remove();
        }
        $('#mainCSSFile').empty().append(cssTemplate);

        $('#templates').injector().invoke(function($compile) {
            var scope = $('#templates').scope();
            $compile(addedBlock)(scope);
        });

        componentIds = $('.dm-template', '#templates');

        $.each(componentIds, function() {
            $(this).removeClass('tempHidden');
        });
        if ( !isReset ) {
            var newTemplate = $('.fake-template + *');
            
            if ( addedBlock.hasClass('dm-menu') ) {
                newTemplate = $('.dm-menu');
            }
            $('.fake-template').css({marginBottom: - $('.fake-template').height()});
            $('.dm-cover + .fake-template + .dm-cover').prev().css({marginBottom: - $('.fake-template').height() - 40});

            setTimeout(function() {
                $('.fake-template').addClass('isCreated');
                setTimeout(function() {
                    if ( $('.menu').length ) {
                        $('.designmodo-wrapper, #previewHolder').find('.menu').each(function(index, element) {
                                    
                            var totalwidth = 0;
                            
                            //check for items
                            if ( $(element).find('.items').exists() ) {
                                var items = $(element).find('.items').width();
                                totalwidth += items;
                            }
                            
                            //check for button
                            if ( $(element).find('.button').exists() ) {
                                var button = $(element).find('.button').width();
                                totalwidth += button;
                            }
                            
                            //check for logo
                            if ( $(element).find('.logo').exists() ) {
                                var logo = $(element).find('.logo > *').width();
                                totalwidth += logo;
                            }
                            
                            //add compact class if menu not fits
                            if ((totalwidth + 152) > $(window).width()){
                                $(element).addClass('compact');
                            } else {
                                $(element).removeClass('compact');
                            }
                        });
                    }
                    $('.fake-template').remove();
                    $('[contenteditable]', addedBlock).addClass('composite');
                }, 500);
            }, 500);
        }
        else {
            $('.fake-template').remove();
            $('.template-reset-holder').remove();
        }
        settingsBlock();
        setTimeout(function() {
            isAdding = false;
        }, 500);
    }

    function settingsBlock() {
        $('.dm-template', '#templates').each(function() {
            var settingsBtn = '<div class="dm-controls dm-settingsBlock-holder" data-html2canvas-ignore>' +
                                    '<div class="dm-control-button">' +
                                        '<div class="dm-control-button-action">' +
                                            '<i class="dm-widget-icon">' +
                                                '<svg width="16px" height="16px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">' +
                                                    '<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">' +
                                                        '<g id="Main-Component-Parameters" sketch:type="MSArtboardGroup" transform="translate(-1382.000000, -129.000000)" fill="#FFFFFF">' +
                                                            '<g id="Group-2" sketch:type="MSLayerGroup" transform="translate(1382.000000, 129.000000)">' +
                                                                '<g id="trash-24" sketch:type="MSShapeGroup">' +
                                                                    '<g id="Remove-Component-3">' +
                                                                        '<path d="M19.72575,9.301875 C19.353125,9.246875 18.953,8.908625 18.8375,8.54975 L18.2765,7.19125 C18.101875,6.857125 18.14175,6.334625 18.37,6.032125 L19.27475,4.824875 C19.503,4.522375 19.48375,4.043875 19.232125,3.760625 L18.238,2.76375 C17.956125,2.512125 17.474875,2.49425 17.172375,2.721125 L15.9665,3.628625 C15.662625,3.854125 15.1415,3.89675 14.806,3.72075 L13.448875,3.161125 C13.08725,3.047 12.747625,2.6455 12.69675,2.270125 L12.48225,0.776875 C12.428625,0.402875 12.078,0.064625 11.70125,0.026125 C11.70125,0.026125 11.468875,0 10.99725,0 C10.525625,0 10.29325,0.026125 10.29325,0.026125 C9.9165,0.064625 9.5645,0.402875 9.51225,0.776875 L9.29775,2.270125 C9.2455,2.6455 8.90725,3.047 8.545625,3.161125 L7.1885,3.72075 C6.854375,3.89675 6.33325,3.854125 6.03075,3.628625 L4.822125,2.721125 C4.521,2.492875 4.03975,2.512125 3.7565,2.76375 L2.76375,3.757875 C2.5135,4.041125 2.49425,4.521 2.721125,4.8235 L3.62725,6.03075 C3.854125,6.33325 3.895375,6.85575 3.719375,7.189875 L3.15975,8.54975 C3.04425,8.908625 2.644125,9.246875 2.270125,9.301875 L0.776875,9.51225 C0.4015,9.56725 0.06325,9.917875 0.02475,10.294625 C0.02475,10.294625 0,10.528375 0,11 C0,11.471625 0.02475,11.70675 0.02475,11.70675 C0.064625,12.082125 0.402875,12.43275 0.776875,12.486375 L2.270125,12.698125 C2.644125,12.75175 3.04425,13.091375 3.15975,13.45025 L3.72075,14.80875 C3.89675,15.14425 3.8555,15.664 3.628625,15.9665 L2.7225,17.175125 C2.495625,17.479 2.440625,17.890125 2.6015,18.088125 C2.761,18.286125 3.221625,18.777 3.223,18.777 C3.223,18.77975 3.378375,18.921375 3.56675,19.091875 C3.755125,19.265125 4.522375,19.504375 4.824875,19.2775 L6.0335,18.37275 C6.336,18.1445 6.857125,18.10325 7.19125,18.27925 L8.545625,18.8375 C8.90725,18.954375 9.2455,19.353125 9.29775,19.7285 L9.51225,21.22175 C9.5645,21.594375 9.9165,21.93675 10.291875,21.97525 C10.291875,21.97525 10.525625,22 10.99725,22 C11.468875,22 11.70125,21.97525 11.70125,21.97525 C12.076625,21.93675 12.428625,21.59575 12.48225,21.22175 L12.69675,19.7285 C12.747625,19.353125 13.085875,18.954375 13.448875,18.8375 L14.804625,18.27925 C15.140125,18.10325 15.66125,18.1445 15.965125,18.37 L17.17375,19.2775 C17.477625,19.504375 17.95475,19.485125 18.239375,19.23625 L19.2335,18.24075 C19.48375,17.9575 19.501625,17.477625 19.273375,17.175125 L18.368625,15.9665 C18.140375,15.664 18.1005,15.14425 18.275125,14.80875 L18.836125,13.45025 C18.951625,13.09 19.35175,12.75175 19.724375,12.698125 L21.217625,12.486375 C21.59025,12.43275 21.929875,12.082125 21.96975,11.70675 C21.96975,11.70675 21.9945,11.473 21.9945,11 C21.9945,10.528375 21.96975,10.294625 21.96975,10.294625 C21.929875,9.917875 21.591625,9.56725 21.217625,9.51225 L19.72575,9.301875 L19.72575,9.301875 Z M11,15.125 C8.723,15.125 6.875,13.277 6.875,11 C6.875,8.7209375 8.723,6.875 11,6.875 C13.2749375,6.875 15.125,8.723 15.125,11 C15.125,13.277 13.277,15.125 11,15.125 L11,15.125 Z" id="Shape-2"></path>' +
                                                                    '</g>' +
                                                                '</g>' +
                                                            '</g>' +
                                                        '</g>' +
                                                    '</g>' +
                                                '</svg>' +
                                            '</i>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="dm-settingsBlock-listHolder">'+
                                        '<ul class="dm-settingsBlock-list">' +
                                            '<li>' +
                                                '<a href="#" class="dm-editing-template">Edit HTML/CSS</a>' +
                                            '</li>' +
                                            '<li>' +
                                                '<a href="#" class="dm-reset-template">Reset</a>' +
                                            '</li>' +
                                            '<li>' +
                                                '<a href="#" class="dm-remove-template">Remove</a>' +
                                            '</li>' +
                                        '</ul>' +
                                    '</div>' +
                                '</div>';
            if (!$('.dm-settingsBlock-holder', $(this)).length) {
                $(this).append(settingsBtn);
            }

            $('.dm-settingsBlock-holder .dm-control-button-action').off('click').on('click', function(e) {
                e.stopPropagation();
                $(this)
                    .closest('.dm-settingsBlock-holder')
                    .toggleClass('show')
                    .removeClass('hideSettings')
                    .closest('.dm-template')
                    .siblings()
                    .find('.dm-settingsBlock-holder')
                    .removeClass('show').addClass('hideSettings');

                $('.dm-popover').removeClass('in');
            });

            $('body').on('keyup', function(e) {
                if ( e.keyCode == 27 ) {
                    hideAllWidgets();
                }
            });
            $(window).on('resize', function() {
                hideAllWidgets();
            });
            function hideAllWidgets() {
                $('.dm-settingsBlock-holder, .dm-controlsView-btns').removeClass('show');
                $('.dm-popover').removeClass('in');
            }
        });

        // Trash Objects
        $('.dm-remove-template').off('click').on('click', function(e) {
            e.preventDefault();
            var isConfirmed = confirm('Are you sure you want to remove this block?');
            if ( isConfirmed ) {
                var template = $(this).closest('[component-id]');
                template.scope().removeTemplate();
                template.remove();
                appendStyles();
                updateControls(hasDifferences());
                $(window).scrollTop($(window).scrollTop() + 1);
                $('.dm-settingsBlock-holder').removeClass('hideSettings');
            }
        });

        // Inline Editing
        $('.dm-editing-template').off('click').on('click', function(e) {
            e.preventDefault();
            var componentId = $(this).closest('.dm-template').attr('component-id');
            $('body').removeClass('preview');
            apiCall(
                'component.template.get', 
                {
                    component_id: componentId,
                    pure_html: true
                },
                function(template) {
                    createEditingPage(template, componentId);
                }
            );
        });
    }

    $('body').on('click', function (event) {
        var target = $(event.target);
        var holders = $('.dm-template');

        if ( target.closest(holders).length !== 0 ) {
            target.closest(holders).addClass('isEdited').siblings().removeClass('isEdited');
        }
        else {
            holders.removeClass('isEdited');
        }

        if ( !$(target[0]).hasClass('dropdown-toggle') ) {
            $('#startMenu').removeClass('open');
        }

        if ((holders.find(target).length !== 0 || holders.filter(target).length) &&
            target.closest('[contenteditable]').length === 0 &&
            target.closest('.dm-text-editing-holder').length === 0 &&
            target.closest('.dm-controlsView-btns').length === 0) {

                target.closest(holders).find('.dm-settingsBlock-holder').toggleClass('visible').removeClass('show').removeClass('hideSettings');
                target.closest(holders).siblings().find('.dm-settingsBlock-holder').removeClass('visible').removeClass('show');
                target.closest('.dm-controlsView-holder').find('.dm-controlsView-btns').toggleClass('show');
                target.closest(holders).siblings().find('.dm-controlsView-btns').removeClass('show');
                if ( target.closest(holders).hasClass('dm-grid') ) {
                    target.closest('li').siblings().find('.dm-controlsView-btns').removeClass('show');
                }
        }
        else if ( target.closest('.minicolors').length === 0 ) {
            $('.dm-controlsView-btns').removeClass('show');
            $('.dm-settingsBlock-holder').removeClass('visible').removeClass('show').removeClass('hideSettings');
        }
        if ( target.hasClass('grid') ) {
            $('.dm-controlsView-btns').removeClass('show');
        }
    });

    function createEditingPage(template, oldComponentId) {
        var editingHolder = $('#editingHolder'),
            editingCSSHolder = $('#editingCSSHolder'),
            editingHTMLHolder = $('#editingHTMLHolder'),
            editingCSS = $('<div id="editingCSS"></div>'),
            editingHTML = $('<div id="editingHTML"></div>'),
            editingBack = $('#editingBack'),
            editingDiscard = $('#editingDiscard'),
            editingSave = $('#editingSave'),
            startCSS = '',
            startHTML = '',
            hasChangedCSS = false,
            hasChangedHTML = false,
            editorCSS,
            editorHTML;

        $('#editingCSS, #editingHTML').remove();
        textChanged();

        editingHTML.empty().html(template.html);
        var _el = $('.dm-template', editingHTML),
            classAttr = {
                'class': _el.attr('class')
            },
            removedParent = _el.unwrap();

        $(editingCSSHolder).append(editingCSS);
        $(editingHTMLHolder).append(editingHTML);

        editingCSS.empty().text(template.custom_css);
        editingHTML.empty().text(removedParent.html());
        startCSS = editingCSS.text();
        startHTML = editingHTML.text();

        ace.config.set("basePath", pluginUrl + "/dist");

        editorCSS = ace.edit("editingCSS");
        editorCSS.getSession().setMode("ace/mode/css");
        editorCSS.getSession().setUseWorker(false);
        editorCSS.setOption("wrap", 120);
        editorCSS.renderer.setShowPrintMargin(false);
        editorCSS.setOption("hScrollBarAlwaysVisible", false);
        editorCSS.find('needle',{
            backwards: false,
            wrap: false,
            caseSensitive: false,
            wholeWord: false,
            regExp: false
        });
        editorCSS.findNext();
        editorCSS.findPrevious();

        editorHTML = ace.edit("editingHTML");
        editorHTML.getSession().setMode("ace/mode/html");
        editorHTML.getSession().setUseWorker(false);
        editorHTML.setOption("wrap", 120);
        editorHTML.renderer.setShowPrintMargin(false);
        editorHTML.setOption("hScrollBarAlwaysVisible", false);
        editorHTML.find('needle',{
            backwards: false,
            wrap: false,
            caseSensitive: false,
            wholeWord: false,
            regExp: false
        });
        editorHTML.findNext();
        editorHTML.findPrevious();

        editingHolder.show();
        leaveHomeScreen('editing');

        editorCSS.on('input', function() {
            hasChangedCSS = (startCSS !== editorCSS.getValue()) ? true : false;
            textChanged();
        });

        editorHTML.on('input', function() {
            hasChangedHTML = (startHTML !== editorHTML.getValue()) ? true : false;
            textChanged();
        });

        editingBack.off('click').on('click', function(e) {
            e.preventDefault();
            removeEditors();
            editingHolder.hide();
            backToHomeScreen();
        });

        editingDiscard.off('click').on('click', function(e) {
            e.preventDefault();
            editorCSS.getSession().setValue(startCSS);
            editorHTML.getSession().setValue(startHTML);
            hasChangedCSS = false;
            hasChangedHTML = false;
            textChanged();
        });

        editingSave.off('click').on('click', function(e) {
            e.preventDefault();

            if(!$(this).hasClass('disabled')) {
                $('#loader-container').addClass('saving');
                var saveCSS = editorCSS.getSession().getValue(),
                    initialCSS = template.css,
                    saveHTML = editorHTML.getSession().getValue(),
                    customTemplateId = template.id;

                if(!template.is_custom) {
                    var serial = Math.floor(Math.random() * 10000000000000000),
                        templateName = template.id.split('.');

                    customTemplateId = 'custom.' + templateName[0] + '_' + templateName[1] + '_' + serial;
                }

                if($('#renderHtml').length) {
                    $('#renderHtml').remove();
                }
                $('body').append('<div id="renderHtml"></div>');
                $('#renderHtml').append(saveHTML);

                var renderHtml = $('#renderHtml');
                renderHtml.children().wrapAll('<div class="dm-template"></div>');
                renderHtml.find('.dm-template')
                    .attr('component-id', '{{ component_id }}')
                    .attr('dm-template', customTemplateId)
                    .addClass(classAttr.class + ' custom-block');

                saveHTML = renderHtml.html();
                renderHtml.remove();

                apiCall(
                    'template.save', 
                    {
                        template_id: customTemplateId,
                        html: saveHTML,
                        css: initialCSS
                    },
                    function(data) {
                        if(data === true) {
                            apiCall(
                                'component.create', 
                                {
                                    template_id: customTemplateId
                                },
                                function(result) {
                                    var componentId = result.component_id;
                                    apiCall(
                                        'component.customcss.set', 
                                        {
                                            component_id: componentId,
                                            custom_css: saveCSS
                                        },
                                        function(result) {
                                            replaceBlock(componentId, oldComponentId, customTemplateId);
                                        },
                                        function(code, msg) { 
                                            alert('Custom CSS is not valid. Layout was not saved.');
                                            $('#loader-container').removeClass('saving');
                                        }
                                    );
                                }
                            );
                        }
                    },
                    function(code, msg) { 
                        alert(msg); 
                        $('#loader-container').removeClass('saving');
                    }
                );
            }
        });

        function replaceBlock(newComponentId, oldComponentId, customTemplateId) {
            apiCall(
                'component.template.get',
                {
                    component_id: newComponentId
                },
                function(template) {
                    $('[component-id="' + oldComponentId + '"]').scope().removeTemplate();
                    $('[component-id="' + oldComponentId + '"]').replaceWith(template.html);

                    var addedBlock = $('[component-id=' + newComponentId + ']', '#templates');
                    addedBlock.addClass('tempHidden');

                    appendStyles(function() {
                        $('#templates').injector().invoke(function($compile) {
                            var scope = $('#templates').scope();
                            $compile(addedBlock)(scope);
                        });
                        settingsBlock();
                        updateControls(hasDifferences());
                        removeEditors();
                        editingHolder.hide();
                        backToHomeScreen();
                    });
                }
            );
        }

        function removeEditors() {
            editorCSS.destroy();
            $(editorCSS.container).remove();
            editorHTML.destroy();
            $(editorHTML.container).remove();
        }

        function textChanged() {
            if(hasChangedCSS || hasChangedHTML) {
                editingDiscard.css({
                    'display': 'block'
                });
                editingSave.removeClass('disabled');
            } else {
                editingDiscard.hide();
                editingSave.addClass('disabled');
            }
        }
    }

    settingsBlock();
    
    function makeScreenshots(items, callback) {
        var images = [],
            counter = 0,
            arrayChanges = $('body').scope().arrayChanges,
            componentIds = [];

        for(var k = 0; k < arrayChanges.length; k++) {
            componentIds.push(arrayChanges[k].id);
        }

        apiCall(
            'component.thumb.get', 
            {
                component_ids: componentIds
            },
            function(data) {
                var reg = /http/;
                async.eachSeries(arrayChanges,                
                    function(item, clbck) {
                        var l = arrayChanges.indexOf(item);
                        var componentId = arrayChanges[l].id,
                            el = $('[component-id="' + componentId + '"]', '#templates'),
                            templateName = el.attr('dm-template').split('.')[0];
                        images[l] = {
                            id: componentId,
                            templateName: templateName
                        };

                        if (!data[componentId] || arrayChanges[l].changed) {
                            el.find('img').each(function(index, imgEl) {
                                imgEl = $(imgEl);
                                if ( reg.test(imgEl.attr('src')) ) {
                                    var par = imgEl.parent();
                                    var mock = $('<div></div>');
                                    mock
                                        .css({'background-color': '#333',
                                            'width': imgEl.width(),
                                            'height': imgEl.height(),
                                            'position': 'absolute',
                                            'top': imgEl.position().top,
                                            'left': imgEl.position().left,
                                            'z-index': 10000})
                                        .addClass('img-mock')
                                    //par.append(mock);
                                }
                            });
                            html2canvas(el, {
                                proxy: pluginUrl + 'html2canvasproxy.php',
                                onrendered: function(canvas) {
                                    counter++;
                                    var previewWidth = 620;
                                    var extra_canvas = document.createElement('canvas');
                                        extra_canvas.setAttribute('width', previewWidth);
                                        extra_canvas.setAttribute('height', canvas.height * previewWidth / canvas.width);
                                    var ctx = extra_canvas.getContext('2d');
                                        ctx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, previewWidth, canvas.height * previewWidth / canvas.width);
                                    var dataURL = extra_canvas.toDataURL("image/jpeg");
                                    images[l].thumb = dataURL;

                                    if(counter == arrayChanges.length) {
                                        callback(images);
                                    }
                                    setTimeout(clbck, 100);
                                },
                                useCORS: true
                            });
                        } else {
                            counter++;
                            images[l].thumb = data[componentId];

                            if(counter == arrayChanges.length) {
                                callback(images);
                            }
                            setTimeout(clbck, 100);
                        }
                    }
                );
            }
        );

        if(!arrayChanges.length) {
            callback(images);
        }
    }

    function createReorderPage(images) {
        var holder = $('<div id="reorderHolder"></div>'),
            blocks = [],
            cache,
            initLayout = [];

        var sortableParams = {
            opacity: 0.75,
            placeholder: "placeholder",
            revert: 300,
            distance: 10,
            refreshPositions: true,
            items: ".reorder-item",
            start: function(event, ui) {
                $('.placeholder').height(ui.item.context.clientHeight);
            },
            update: function() {
                checkChanges();
            }
        };

        if($('#reorderHolder').length) {
            $('#reorderHolder').remove();
        }
        for(var i = 0; i < images.length; i++) {
            if(images[i].templateName !== 'menu') {
                blocks.push('<div data-reorder-component-id="' + images[i].id + '" class="reorder-item"><img src="' + images[i].thumb + '" /></div>');
            } else {
                blocks.push('<div data-reorder-component-id="' + images[i].id + '"><img src="' + images[i].thumb + '" /></div>');
            }
            initLayout.push(images[i].id);
        }
        leaveHomeScreen('reorder');
        $('#loader-container').removeClass('saving');
        $('body').append(holder);
        $('#reorderHolder').append(blocks.join('\n\n'));
        $("#reorderHolder").sortable(sortableParams);
        cache = $("#reorderHolder").html();
        updateReorderControls(false);

        $('#reorderBack').off('click').on('click', function(e) {
            e.preventDefault();
            backToHomeScreen();
        });

        $('#reorderDiscard').off('click').on('click', function(e) {
            e.preventDefault();
            updateReorderControls(false);
            $("#reorderHolder").html(cache).sortable("refresh");
        });

        $('#reorderSave').off('click').on('click', function(e) {
            e.preventDefault();
            var reorderedArr = [],
                items = $('[component-id]', '#templates');

            if(!$(this).hasClass('disabled')) {
                $('.reorder-item', '#reorderHolder').each(function() {
                    reorderedArr.push(+$(this).data('reorder-component-id'));
                });
                for(var i = 0; i < reorderedArr.length; i++) {
                    $('#templates').append(items.filter('[component-id="' + reorderedArr[i] + '"]'));
                }
                $('body').scope().reorderBlocks();
                updateControls(hasDifferences());
                backToHomeScreen();
            }
        });

        function checkChanges() {
            var components = $('.reorder-item', '#reorderHolder'),
                reorderLayout = [],
                changed = false;

            components.each(function() {
                reorderLayout.push($(this).data('reorder-component-id'));
            });

            for(var i = 0; i < reorderLayout.length; i++) {
                if(reorderLayout[i] != initLayout[i]) {
                    changed = true;
                    break;
                }
            }
            updateReorderControls(changed);
        }
    }

    function updateReorderControls(show) {
        if(show) {
            $('#reorderSave').removeClass('disabled');
            $('#reorderDiscard').css({
                'display': 'block'
            });
        } else {
            $('#reorderSave').addClass('disabled');
            $('#reorderDiscard').hide();
        }
    }

    // Reorder Blocks
    $('#reorderBlocks').on('click', function(e) {
        $('.composite').removeClass('composite');
        e.preventDefault();

        var components = [];

        $('[component-id]', '#templates').each(function() {
            components.push(+$(this).attr('component-id'));
        });
        
        if ( !components.length ) {
            alert('There are no blocks to reorder!');
            return false;
        }

        $('body').removeClass('preview').addClass('reorder-mode');
        $('#loader-container').addClass('saving');

        makeScreenshots(components, function(images) {
            createReorderPage(images);
        });
    });

    function postTitleChange() {
        var title = $('#postTitle');

        title.init = '';
        title.on('focus', function() {
            title.init = title.val();
        });
        title.on('keyup', function() {
            title.changed = title.val();
            if ( title.init != title.changed ) {
                updateControls(true);
            }
            else {
                updateControls(hasDifferences());
            }
        });
    }
    postTitleChange();

    // Save layout
    $('#saveChanges').on('click', function(e) {
        e.preventDefault();

        $('[contenteditable]').removeClass('composite');
        // Save page title
        apiCall(
            'post.title.set', 
            {
                post_id: currentPostId,
                title: $('#postTitle').val()
            },
            function(result) {
                if (result) {
                    document.title = $('#postTitle').val();
                }
            }
        );

        var saveData = [],
            components = [],
            counter = 0;

        $('[component-id]', '#templates').each(function() {
            components.push(+$(this).attr('component-id'));
        });

        if ( !components.length ) {
            alert('You cannot save an empty page!');
            return false;
        }

        if(!$(this).hasClass('disabled')) {
            $('body').removeClass('preview');
            $('#loader-container').addClass('saving');

            setTimeout(function() {
                makeScreenshots(components, function(images) {
                    imagesRendered(images);
                });
            }, 100);
            $('#startMenu').removeClass('open');
        }

        function imagesRendered(saveData) {
            apiCall(
                'layout.save',
                {
                    layout_id: currentLayoutId,
                    components: saveData
                },
                function(layoutId) {
                    $('body').scope().save();
                    getOriginalLayout();
                    $('[contenteditable]').addClass('composite');
                    updateControls(hasDifferences());
                }
            );
        }
    });

    // Discard all changes
    $('body').on('click', '.dm-reset-template', function(e) {
        e.preventDefault();

        var btn = $(e.target);

        apiCall(
            'layout.components.get',
            {
                layout_id: currentLayoutId
            },
            function(components) {
                var restoreTemplates = components,
                    tmpBlocks = [],
                    comp = btn.closest('[component-id]'),
                    compId = comp.attr('component-id'),
                    compTemplate = comp.attr('dm-template'),
                    compHasClass = comp.hasClass('custom-block'),
                    compExists = false,
                    itemHtml;

                var isConfirmed = confirm('Are you sure you want to reset this block?');
                window.templateScreenshot = '';
                if ( isConfirmed ) {
                    comp.after('<div class="template-reset-holder" />');
                    $('.template-reset-holder').height(comp.outerHeight());
                    comp.scope().removeTemplate();
                    comp.remove();
                    $.each(components, function() {
                        if ( $(this)[0].id == compId ) {
                            restoreTemplates = $(this);
                            compExists = true;
                        }
                    });
                    if ( compHasClass ) {
                        compTemplate = compTemplate.substr(compTemplate.indexOf('custom') + 7).replace('_', '.').split('_', 1)[0];
                    }
                    if ( compExists ) {
                        for(var i = 0; i < restoreTemplates.length; i++) {
                            tmpBlocks.push('<div id="temp-' + restoreTemplates[i]['id'] + '"></div>');
                        }
                    }
                    else {
                        tmpBlocks.push('<div id="temp-' + compId + '"></div>');
                    }
                    generateTemplate(compTemplate, 'draggable', true, $('.template-reset-holder'));
                }
                return false;
            }
        );
    });

    $('#previewLayout').on('click', function(e) {
        e.preventDefault();
        $('#previewMenu > .title').html($('#postTitle').val());
        $('body').removeClass('preview');
        $('#sideMenu').addClass('noHover');
        var layoutsArray = [],
            components = $('[component-id]', '#templates'),
            componentIdArray = [];

        components.each(function() {
            var $this = $(this),
                sendObj = {};

            sendObj.template_id = $this.attr('dm-template');
            sendObj.component_id = $this.attr('component-id');
            if($this.scope().findById) {
                sendObj.model = $this.scope().findById();
            }
            layoutsArray.push(sendObj);
        });

        if ( !components[0] ) {
            alert('There are no blocks to preview!');
            return false;
        }

        apiCall(
            'layout.preview.get', 
            {
                components: layoutsArray
            },
            function(result) {
                createPreviewPage(result);
                $('.custom-img img').each(function() {
                    var imgText = $(this).attr('src');

                    if ( imgText.indexOf('1x1.png') > -1 ) {
                        $(this).addClass('removed-image');
                    }
                });
           }
        );
    });

    function createPreviewPage(result) {
        var holder = $('<div id="previewHolder"><div class="previewWrapper"></div></div>');

        if($('#previewHolder').length) {
            $('#previewHolder').remove();
        }
        leaveHomeScreen('preview');
        $('body').append(holder);
        $('#mainCSSFile').append(result.css);
        $('#previewHolder .previewWrapper').append(result.html);
        var blocks = [];
        $('#previewHolder').find('[component-id]').each(function(index, element) {
            var el = $(element);
            var tmpId = el.attr('dm-template');
            var type = tmpId.split('.')[0];
            if (type == 'custom') {
                type = tmpId.split('.')[1].split('_')[0];
            }

            if (blocks.indexOf(type) == -1) {
                blocks.push(type);
            }
        });

        var el = $('#previewHolder');
        for (var i = 0; i < blocks.length; i++) {
            var scrUrl = window.scriptsUrls[blocks[i]];
            window.loadExternalScript(el, scrUrl, false);
        }

        $('#previewBack').off('click').on('click', function(e) {
            e.preventDefault();
            backToHomeScreen();
        });
    }

    function leaveHomeScreen(typeOfPage) {
        $('.designmodo-wrapper').css({position: 'fixed', opacity: 0, left: 0, top: 0, right: 0, zIndex: -1});
        $('#menu').hide();
        $('#startMenu').addClass('disabledMenu');
        $('#' + typeOfPage + 'Menu').removeClass('disabledMenu');
        setTimeout(function() {
            $(window).resize();
        },500);
    }

    function backToHomeScreen() {
        $('[contenteditable]').addClass('composite');
        $('#previewMenu, #reorderMenu, #editingMenu').addClass('disabledMenu');
        $('#loader-container').removeClass('saving');
        $('#startMenu').removeClass('disabledMenu');
        $('.hideSettings').removeClass('hideSettings');
        $('.designmodo-wrapper').css({position: 'relative', opacity: 1, zIndex: 2});
        $('.designmodo-wrapper').css({marginLeft: 0});
        $('body').removeClass('reorder-mode');
        setTimeout(function() {
            $('#menu').show();
        }, 500);
        $('#previewHolder, #reorderHolder').remove();

        $(window).resize();
    }

    function getOriginalLayout() {
        originalLayout = [];
        $('[component-id]', '#templates').each(function() {
            originalLayout.push($(this).attr('component-id'));
        });
    }

    getOriginalLayout();

    window.updateControls = function(show) {
        if(show) {
            $('#saveChanges').removeClass('disabled');
        } else {
            $('#saveChanges').addClass('disabled');
            $('#loader-container').removeClass('saving');
        }
    };

    window.hasDifferences = function() {
        var arrayChanges = $('body').scope().arrayChanges;

        if (!arrayChanges)
            return;

        if(Object.keys(arrayChanges).length !== originalLayout.length) {
            return true;
        } else {
            for(var k = 0; k < arrayChanges.length; k++) {
                if(arrayChanges[k].changed || arrayChanges[k].id !== originalLayout[k]) {
                    return true;
                }
            }
        }
        return false;
    };

    function appendStyles(callback) {
        var componentIds = $('.dm-template', '#templates'),
            cssArray = [],
            hrefCSS;

        $.each(componentIds, function() {
            cssArray.push(+$(this).attr('component-id'));
        });

        if(cssArray.length) {
            apiCall(
                'component.css.get',
                {
                    component_ids: cssArray,
                    format: 'json'
                }, 
                function(css) {
                    if($('#initCSSFile').length) {
                        $('#initCSSFile').remove();
                    }
                    $('#mainCSSFile').empty().append(css);
                    $.each(componentIds, function() {
                        $(this).removeClass('tempHidden');
                    });
                    if(callback) {
                        callback();
                    }
                }
            );
        }
    }

    $('body').scope().$watch('arrayChanges', function(newNames) {
        updateControls(hasDifferences());
    }, true);

    $(window).bind('beforeunload',function() {
        if(hasDifferences()) {
            return 'Please save you changes before leaving.';
        }
    });

    $('body').on('click', function (e) {
        if($(e.target).parents('#menu').length === 0) {
            $('#subMenu').addClass('invisible');
            $('#sideMenu').addClass('noHover');
            $('body').removeClass('preview');
        }
    });
});

// additional functions
$.fn.preload = function() {
    this.each(function() {
        $('<img/>')[0].src = this;
    });
};
$.fn.exists = function() {
    return this.length > 0;
};
//extend for draggable
var oldMouseStart = $.ui.draggable.prototype._mouseStart;
$.ui.draggable.prototype._mouseStart = function(event, overrideHandle, noActivation) {
    this._trigger("beforeStart", event, this._uiHash());
    oldMouseStart.apply(this, [event, overrideHandle, noActivation]);
};

/**
 * Call API method
 * @param String method
 * @param PlainObject data
 * @param Function success
 * @param Function failure
 */
function apiCall(method, data, success, failure) {
    var defaultSuccess = function(result) {
        console.log(result);
    };
    var defaultFailure = function(code, msg) {
        alert("API failed: " + code + ": " + msg);
    };
    success = success || defaultSuccess;
    failure = failure || defaultFailure;
    
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'dm_api',
            method: method,
            params: data
        },
        dataType: 'json',
        success: function(response) {
            if ('result' in response) {
                success(response.result);
            } else if ('error' in response) {
                failure(response.error.code, response.error.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            failure(textStatus, errorThrown);
        }
    });
}

jQuery(function($) {
    $('body').on('mouseenter', '.dm-settingsBlock-holder', function() {
        var button = $(this),
            buttonHeight = button.height(),
            buttonTop = button.offset().top,
            documentHeight = $(document).height(),
            dropDown = button.find('.dm-settingsBlock-listHolder'),
            dropDownHeight = dropDown.height();

        if (documentHeight < buttonTop + buttonHeight + dropDownHeight) {
            dropDown.addClass('showTop');
        }
        else {
            dropDown.removeClass('showTop');
        }
    });

    $('body').on('click', '.dm-carousel-add, .dm-carousel-remove', function(e) {
        e.preventDefault();
    });

    $('.dm-carousel-hide').on('mouseenter', function() {
        var width = $(this).find('a').first().outerWidth() + $(this).find('a').last().outerWidth();
        $(this).stop().animate({width: width}, 250);
    });

    $('.dm-carousel-hide').on('mouseleave', function() {
        var width = $(this).find('.dm-carousel-counter').outerWidth();
        $(this).stop().animate({width: width},250);
    });

    $('.custom-img img').each(function() {
        var imgText = $(this).attr('src');

        if ( imgText.indexOf('1x1.png') > -1 ) {
            $(this).addClass('removed-image');
        }
    });

    $('.dropdown-toggle').on('click', function() {
        $('#startMenu').toggleClass('open');
    });
    $(window).on('resize', function() {
        $('#startMenu').removeClass('open');
    });
    if ( $('.dm-template').length ) {
        $('body').removeClass('preview');
        $('#sideMenu').addClass('noHover');
        $('#subMenu').addClass('invisible');
    }
    else {
        $('body').addClass('preview');
        $('#sideMenu').removeClass('noHover');
    }
    $(window).on('load', function() {
        $('[contenteditable]').addClass('composite');
    });
    window.isAdding = false;
});

(function () {
	angular
		.module('dmApp')
		.service('compatibility', function () {
			/**
			 * Constant for checking FF
			 * @type {boolean}
			 */
			this.isFirefox = typeof InstallTrigger !== 'undefined';

			/**
			 * Constant for checking IE
			 * @type {boolean}
			 */
			this.isIE = !!document.documentMode;

			/**
			 * Constant for checking Opera
			 * @type {boolean}
			 */
			this.isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

			/**
			 * Constant for checking Chrome
			 * @type {boolean}
			 */
			this.isChrome = !!window.chrome && !this.isOpera;

			return this;
		});
})();

(function () {
	angular
		.module('dmApp')
		.service('fonts', ['fontsFactory', '$rootScope', function (fontsFactory, $rootScope) {
			var webFonts = [];
			var googleFonts = [];

			fontsFactory.getFonts().success(function(data) {
				var wFonts = data.family.webFonts;
				var gFonts = data.family.googleFonts;

				var key;
				for (key in wFonts) {
					if (wFonts.hasOwnProperty(key)) {
						webFonts.push({key: key, value: wFonts[key]});
					}
				}

				for (key in gFonts) {
					if (gFonts.hasOwnProperty(key)) {
						googleFonts.push({key: key, value: gFonts[key]});
					}
				}

				$rootScope.$emit('loaded-fonts');
			});

			this.getWebFonts = function () {
				return webFonts;
			};

			this.getGoogleFonts = function () {
				return googleFonts;
			};

			return this;
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.service('templateModel', [function() {
			function Template(id, data) {
				this.id = id;
				this.data = data;
			}

			this.create = function(id, data) {
				return new Template(id, data);
			};
		}]);
})();

/*
  html2canvas 0.4.1 <http://html2canvas.hertzen.com>
  Copyright (c) 2013 Niklas von Hertzen

  Released under MIT License
*/

(function(window, document, undefined){

"use strict";

var _html2canvas = {},
previousElement,
computedCSS,
html2canvas;

_html2canvas.Util = {};

_html2canvas.Util.log = function(a) {
  if (_html2canvas.logging && window.console && window.console.log) {
    window.console.log(a);
  }
};

_html2canvas.Util.trimText = (function(isNative){
  return function(input) {
    return isNative ? isNative.apply(input) : ((input || '') + '').replace( /^\s+|\s+$/g , '' );
  };
})(String.prototype.trim);

_html2canvas.Util.asFloat = function(v) {
  return parseFloat(v);
};

(function() {
  // TODO: support all possible length values
  var TEXT_SHADOW_PROPERTY = /((rgba|rgb)\([^\)]+\)(\s-?\d+px){0,})/g;
  var TEXT_SHADOW_VALUES = /(-?\d+px)|(#.+)|(rgb\(.+\))|(rgba\(.+\))/g;
  _html2canvas.Util.parseTextShadows = function (value) {
    if (!value || value === 'none') {
      return [];
    }

    // find multiple shadow declarations
    var shadows = value.match(TEXT_SHADOW_PROPERTY),
      results = [];
    for (var i = 0; shadows && (i < shadows.length); i++) {
      var s = shadows[i].match(TEXT_SHADOW_VALUES);
      results.push({
        color: s[0],
        offsetX: s[1] ? s[1].replace('px', '') : 0,
        offsetY: s[2] ? s[2].replace('px', '') : 0,
        blur: s[3] ? s[3].replace('px', '') : 0
      });
    }
    return results;
  };
})();


_html2canvas.Util.parseBackgroundImage = function (value) {
    var whitespace = ' \r\n\t',
        method, definition, prefix, prefix_i, block, results = [],
        c, mode = 0, numParen = 0, quote, args;

    var appendResult = function(){
        if(method) {
            if(definition.substr( 0, 1 ) === '"') {
                definition = definition.substr( 1, definition.length - 2 );
            }
            if(definition) {
                args.push(definition);
            }
            if(method.substr( 0, 1 ) === '-' &&
                    (prefix_i = method.indexOf( '-', 1 ) + 1) > 0) {
                prefix = method.substr( 0, prefix_i);
                method = method.substr( prefix_i );
            }
            results.push({
                prefix: prefix,
                method: method.toLowerCase(),
                value: block,
                args: args
            });
        }
        args = []; //for some odd reason, setting .length = 0 didn't work in safari
        method =
            prefix =
            definition =
            block = '';
    };

    appendResult();
    for(var i = 0, ii = value.length; i<ii; i++) {
        c = value[i];
        if(mode === 0 && whitespace.indexOf( c ) > -1){
            continue;
        }
        switch(c) {
            case '"':
                if(!quote) {
                    quote = c;
                }
                else if(quote === c) {
                    quote = null;
                }
                break;

            case '(':
                if(quote) { break; }
                else if(mode === 0) {
                    mode = 1;
                    block += c;
                    continue;
                } else {
                    numParen++;
                }
                break;

            case ')':
                if(quote) { break; }
                else if(mode === 1) {
                    if(numParen === 0) {
                        mode = 0;
                        block += c;
                        appendResult();
                        continue;
                    } else {
                        numParen--;
                    }
                }
                break;

            case ',':
                if(quote) { break; }
                else if(mode === 0) {
                    appendResult();
                    continue;
                }
                else if (mode === 1) {
                    if(numParen === 0 && !method.match(/^url$/i)) {
                        args.push(definition);
                        definition = '';
                        block += c;
                        continue;
                    }
                }
                break;
        }

        block += c;
        if(mode === 0) { method += c; }
        else { definition += c; }
    }
    appendResult();

    return results;
};

_html2canvas.Util.Bounds = function (element) {
  var clientRect, bounds = {};

  if (element.getBoundingClientRect){
    clientRect = element.getBoundingClientRect();

    // TODO add scroll position to bounds, so no scrolling of window necessary
    bounds.top = clientRect.top;
    bounds.bottom = clientRect.bottom || (clientRect.top + clientRect.height);
    bounds.left = clientRect.left;

    bounds.width = element.offsetWidth;
    bounds.height = element.offsetHeight;
  }

  return bounds;
};

// TODO ideally, we'd want everything to go through this function instead of Util.Bounds,
// but would require further work to calculate the correct positions for elements with offsetParents
_html2canvas.Util.OffsetBounds = function (element) {
  var parent = element.offsetParent ? _html2canvas.Util.OffsetBounds(element.offsetParent) : {top: 0, left: 0};

  return {
    top: element.offsetTop + parent.top,
    bottom: element.offsetTop + element.offsetHeight + parent.top,
    left: element.offsetLeft + parent.left,
    width: element.offsetWidth,
    height: element.offsetHeight
  };
};

function toPX(element, attribute, value ) {
    var rsLeft = element.runtimeStyle && element.runtimeStyle[attribute],
        left,
        style = element.style;

    // Check if we are not dealing with pixels, (Opera has issues with this)
    // Ported from jQuery css.js
    // From the awesome hack by Dean Edwards
    // http://erik.eae.net/archives/2007/07/27/18.54.15/#comment-102291

    // If we're not dealing with a regular pixel number
    // but a number that has a weird ending, we need to convert it to pixels

    if ( !/^-?[0-9]+\.?[0-9]*(?:px)?$/i.test( value ) && /^-?\d/.test(value) ) {
        // Remember the original values
        left = style.left;

        // Put in the new values to get a computed value out
        if (rsLeft) {
            element.runtimeStyle.left = element.currentStyle.left;
        }
        style.left = attribute === "fontSize" ? "1em" : (value || 0);
        value = style.pixelLeft + "px";

        // Revert the changed values
        style.left = left;
        if (rsLeft) {
            element.runtimeStyle.left = rsLeft;
        }
    }

    if (!/^(thin|medium|thick)$/i.test(value)) {
        return Math.round(parseFloat(value)) + "px";
    }

    return value;
}

function asInt(val) {
    return parseInt(val, 10);
}

function parseBackgroundSizePosition(value, element, attribute, index) {
    value = (value || '').split(',');
    value = value[index || 0] || value[0] || 'auto';
    value = _html2canvas.Util.trimText(value).split(' ');
    if(attribute === 'backgroundSize' && (value[0] && value[0].match(/^(cover|contain|auto)$/))) {
        return value;
    } else {
        value[0] = (value[0].indexOf( "%" ) === -1) ? toPX(element, attribute + "X", value[0]) : value[0];
        if(value[1] === undefined) {
            if(attribute === 'backgroundSize') {
                value[1] = 'auto';
                return value;
            } else {
                // IE 9 doesn't return double digit always
                value[1] = value[0];
            }
        }
        value[1] = (value[1].indexOf("%") === -1) ? toPX(element, attribute + "Y", value[1]) : value[1];
    }
    return value;
}

_html2canvas.Util.getCSS = function (element, attribute, index) {
    if (previousElement !== element) {
      computedCSS = document.defaultView.getComputedStyle(element, null);
    }

    var value = computedCSS[attribute];

    if (/^background(Size|Position)$/.test(attribute)) {
        return parseBackgroundSizePosition(value, element, attribute, index);
    } else if (/border(Top|Bottom)(Left|Right)Radius/.test(attribute)) {
      var arr = value.split(" ");
      if (arr.length <= 1) {
          arr[1] = arr[0];
      }
      return arr.map(asInt);
    }

  return value;
};

_html2canvas.Util.resizeBounds = function( current_width, current_height, target_width, target_height, stretch_mode ){
  var target_ratio = target_width / target_height,
    current_ratio = current_width / current_height,
    output_width, output_height;

  if(!stretch_mode || stretch_mode === 'auto') {
    output_width = target_width;
    output_height = target_height;
  } else if(target_ratio < current_ratio ^ stretch_mode === 'contain') {
    output_height = target_height;
    output_width = target_height * current_ratio;
  } else {
    output_width = target_width;
    output_height = target_width / current_ratio;
  }

  return {
    width: output_width,
    height: output_height
  };
};

_html2canvas.Util.BackgroundPosition = function(element, bounds, image, imageIndex, backgroundSize ) {
    var backgroundPosition =  _html2canvas.Util.getCSS(element, 'backgroundPosition', imageIndex),
        leftPosition,
        topPosition;
    if (backgroundPosition.length === 1){
        backgroundPosition = [backgroundPosition[0], backgroundPosition[0]];
    }

    if (backgroundPosition[0].toString().indexOf("%") !== -1){
        leftPosition = (bounds.width - (backgroundSize || image).width) * (parseFloat(backgroundPosition[0]) / 100);
    } else {
        leftPosition = parseInt(backgroundPosition[0], 10);
    }

    if (backgroundPosition[1] === 'auto') {
        topPosition = leftPosition / image.width * image.height;
    } else if (backgroundPosition[1].toString().indexOf("%") !== -1){
        topPosition =  (bounds.height - (backgroundSize || image).height) * parseFloat(backgroundPosition[1]) / 100;
    } else {
        topPosition = parseInt(backgroundPosition[1], 10);
    }

    if (backgroundPosition[0] === 'auto') {
        leftPosition = topPosition / image.height * image.width;
    }

    return {left: leftPosition, top: topPosition};
};

_html2canvas.Util.BackgroundSize = function(element, bounds, image, imageIndex) {
    var backgroundSize =  _html2canvas.Util.getCSS(element, 'backgroundSize', imageIndex),
        width,
        height;

    if (backgroundSize.length === 1){
        backgroundSize = [backgroundSize[0], backgroundSize[0]];
    }

    if (backgroundSize[0].toString().indexOf("%") !== -1){
        width = bounds.width * parseFloat(backgroundSize[0]) / 100;
    } else if(backgroundSize[0] === 'auto') {
        width = image.width;
    } else {
        if (/contain|cover/.test(backgroundSize[0])) {
            var resized = _html2canvas.Util.resizeBounds(image.width, image.height, bounds.width, bounds.height, backgroundSize[0]);
            return {width: resized.width, height: resized.height};
        } else {
            width = parseInt(backgroundSize[0], 10);
        }
    }

    if(backgroundSize[1] === 'auto') {
        height = width / image.width * image.height;
    } else if (backgroundSize[1].toString().indexOf("%") !== -1){
        height =  bounds.height * parseFloat(backgroundSize[1]) / 100;
    } else {
        height = parseInt(backgroundSize[1],10);
    }


    if (backgroundSize[0] === 'auto') {
        width = height / image.height * image.width;
    }

    return {width: width, height: height};
};

_html2canvas.Util.Extend = function (options, defaults) {
  for (var key in options) {
    if (options.hasOwnProperty(key)) {
      defaults[key] = options[key];
    }
  }
  return defaults;
};


/*
 * Derived from jQuery.contents()
 * Copyright 2010, John Resig
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 */
_html2canvas.Util.Children = function( elem ) {
  var children;
  try {
    children = (elem.nodeName && elem.nodeName.toUpperCase() === "IFRAME") ? elem.contentDocument || elem.contentWindow.document : (function(array) {
      var ret = [];
      if (array !== null) {
        (function(first, second ) {
          var i = first.length,
          j = 0;

          if (typeof second.length === "number") {
            for (var l = second.length; j < l; j++) {
              first[i++] = second[j];
            }
          } else {
            while (second[j] !== undefined) {
              first[i++] = second[j++];
            }
          }

          first.length = i;

          return first;
        })(ret, array);
      }
      return ret;
    })(elem.childNodes);

  } catch (ex) {
    _html2canvas.Util.log("html2canvas.Util.Children failed with exception: " + ex.message);
    children = [];
  }
  return children;
};

_html2canvas.Util.isTransparent = function(backgroundColor) {
  return (!backgroundColor || backgroundColor === "transparent" || backgroundColor === "rgba(0, 0, 0, 0)");
};
_html2canvas.Util.Font = (function () {

  var fontData = {};

  return function(font, fontSize, doc) {
    if (fontData[font + "-" + fontSize] !== undefined) {
      return fontData[font + "-" + fontSize];
    }

    var container = doc.createElement('div'),
    img = doc.createElement('img'),
    span = doc.createElement('span'),
    sampleText = 'Hidden Text',
    baseline,
    middle,
    metricsObj;

    container.style.visibility = "hidden";
    container.style.fontFamily = font;
    container.style.fontSize = fontSize;
    container.style.margin = 0;
    container.style.padding = 0;

    doc.body.appendChild(container);

    // http://probablyprogramming.com/2009/03/15/the-tiniest-gif-ever (handtinywhite.gif)
    img.src = "data:image/gif;base64,R0lGODlhAQABAIABAP///wAAACwAAAAAAQABAAACAkQBADs=";
    img.width = 1;
    img.height = 1;

    img.style.margin = 0;
    img.style.padding = 0;
    img.style.verticalAlign = "baseline";

    span.style.fontFamily = font;
    span.style.fontSize = fontSize;
    span.style.margin = 0;
    span.style.padding = 0;

    span.appendChild(doc.createTextNode(sampleText));
    container.appendChild(span);
    container.appendChild(img);
    baseline = (img.offsetTop - span.offsetTop) + 1;

    container.removeChild(span);
    container.appendChild(doc.createTextNode(sampleText));

    container.style.lineHeight = "normal";
    img.style.verticalAlign = "super";

    middle = (img.offsetTop-container.offsetTop) + 1;
    metricsObj = {
      baseline: baseline,
      lineWidth: 1,
      middle: middle
    };

    fontData[font + "-" + fontSize] = metricsObj;

    doc.body.removeChild(container);

    return metricsObj;
  };
})();

(function(){
  var Util = _html2canvas.Util,
    Generate = {};

  _html2canvas.Generate = Generate;

  var reGradients = [
  /^(-webkit-linear-gradient)\(([a-z\s]+)([\w\d\.\s,%\(\)]+)\)$/,
  /^(-o-linear-gradient)\(([a-z\s]+)([\w\d\.\s,%\(\)]+)\)$/,
  /^(-webkit-gradient)\((linear|radial),\s((?:\d{1,3}%?)\s(?:\d{1,3}%?),\s(?:\d{1,3}%?)\s(?:\d{1,3}%?))([\w\d\.\s,%\(\)\-]+)\)$/,
  /^(-moz-linear-gradient)\(((?:\d{1,3}%?)\s(?:\d{1,3}%?))([\w\d\.\s,%\(\)]+)\)$/,
  /^(-webkit-radial-gradient)\(((?:\d{1,3}%?)\s(?:\d{1,3}%?)),\s(\w+)\s([a-z\-]+)([\w\d\.\s,%\(\)]+)\)$/,
  /^(-moz-radial-gradient)\(((?:\d{1,3}%?)\s(?:\d{1,3}%?)),\s(\w+)\s?([a-z\-]*)([\w\d\.\s,%\(\)]+)\)$/,
  /^(-o-radial-gradient)\(((?:\d{1,3}%?)\s(?:\d{1,3}%?)),\s(\w+)\s([a-z\-]+)([\w\d\.\s,%\(\)]+)\)$/
  ];

  /*
 * TODO: Add IE10 vendor prefix (-ms) support
 * TODO: Add W3C gradient (linear-gradient) support
 * TODO: Add old Webkit -webkit-gradient(radial, ...) support
 * TODO: Maybe some RegExp optimizations are possible ;o)
 */
  Generate.parseGradient = function(css, bounds) {
    var gradient, i, len = reGradients.length, m1, stop, m2, m2Len, step, m3, tl,tr,br,bl;

    for(i = 0; i < len; i+=1){
      m1 = css.match(reGradients[i]);
      if(m1) {
        break;
      }
    }

    if(m1) {
      switch(m1[1]) {
        case '-webkit-linear-gradient':
        case '-o-linear-gradient':

          gradient = {
            type: 'linear',
            x0: null,
            y0: null,
            x1: null,
            y1: null,
            colorStops: []
          };

          // get coordinates
          m2 = m1[2].match(/\w+/g);
          if(m2){
            m2Len = m2.length;
            for(i = 0; i < m2Len; i+=1){
              switch(m2[i]) {
                case 'top':
                  gradient.y0 = 0;
                  gradient.y1 = bounds.height;
                  break;

                case 'right':
                  gradient.x0 = bounds.width;
                  gradient.x1 = 0;
                  break;

                case 'bottom':
                  gradient.y0 = bounds.height;
                  gradient.y1 = 0;
                  break;

                case 'left':
                  gradient.x0 = 0;
                  gradient.x1 = bounds.width;
                  break;
              }
            }
          }
          if(gradient.x0 === null && gradient.x1 === null){ // center
            gradient.x0 = gradient.x1 = bounds.width / 2;
          }
          if(gradient.y0 === null && gradient.y1 === null){ // center
            gradient.y0 = gradient.y1 = bounds.height / 2;
          }

          // get colors and stops
          m2 = m1[3].match(/((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\)(?:\s\d{1,3}(?:%|px))?)+/g);
          if(m2){
            m2Len = m2.length;
            step = 1 / Math.max(m2Len - 1, 1);
            for(i = 0; i < m2Len; i+=1){
              m3 = m2[i].match(/((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\))\s*(\d{1,3})?(%|px)?/);
              if(m3[2]){
                stop = parseFloat(m3[2]);
                if(m3[3] === '%'){
                  stop /= 100;
                } else { // px - stupid opera
                  stop /= bounds.width;
                }
              } else {
                stop = i * step;
              }
              gradient.colorStops.push({
                color: m3[1],
                stop: stop
              });
            }
          }
          break;

        case '-webkit-gradient':

          gradient = {
            type: m1[2] === 'radial' ? 'circle' : m1[2], // TODO: Add radial gradient support for older mozilla definitions
            x0: 0,
            y0: 0,
            x1: 0,
            y1: 0,
            colorStops: []
          };

          // get coordinates
          m2 = m1[3].match(/(\d{1,3})%?\s(\d{1,3})%?,\s(\d{1,3})%?\s(\d{1,3})%?/);
          if(m2){
            gradient.x0 = (m2[1] * bounds.width) / 100;
            gradient.y0 = (m2[2] * bounds.height) / 100;
            gradient.x1 = (m2[3] * bounds.width) / 100;
            gradient.y1 = (m2[4] * bounds.height) / 100;
          }

          // get colors and stops
          m2 = m1[4].match(/((?:from|to|color-stop)\((?:[0-9\.]+,\s)?(?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\)\))+/g);
          if(m2){
            m2Len = m2.length;
            for(i = 0; i < m2Len; i+=1){
              m3 = m2[i].match(/(from|to|color-stop)\(([0-9\.]+)?(?:,\s)?((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\))\)/);
              stop = parseFloat(m3[2]);
              if(m3[1] === 'from') {
                stop = 0.0;
              }
              if(m3[1] === 'to') {
                stop = 1.0;
              }
              gradient.colorStops.push({
                color: m3[3],
                stop: stop
              });
            }
          }
          break;

        case '-moz-linear-gradient':

          gradient = {
            type: 'linear',
            x0: 0,
            y0: 0,
            x1: 0,
            y1: 0,
            colorStops: []
          };

          // get coordinates
          m2 = m1[2].match(/(\d{1,3})%?\s(\d{1,3})%?/);

          // m2[1] == 0%   -> left
          // m2[1] == 50%  -> center
          // m2[1] == 100% -> right

          // m2[2] == 0%   -> top
          // m2[2] == 50%  -> center
          // m2[2] == 100% -> bottom

          if(m2){
            gradient.x0 = (m2[1] * bounds.width) / 100;
            gradient.y0 = (m2[2] * bounds.height) / 100;
            gradient.x1 = bounds.width - gradient.x0;
            gradient.y1 = bounds.height - gradient.y0;
          }

          // get colors and stops
          m2 = m1[3].match(/((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\)(?:\s\d{1,3}%)?)+/g);
          if(m2){
            m2Len = m2.length;
            step = 1 / Math.max(m2Len - 1, 1);
            for(i = 0; i < m2Len; i+=1){
              m3 = m2[i].match(/((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\))\s*(\d{1,3})?(%)?/);
              if(m3[2]){
                stop = parseFloat(m3[2]);
                if(m3[3]){ // percentage
                  stop /= 100;
                }
              } else {
                stop = i * step;
              }
              gradient.colorStops.push({
                color: m3[1],
                stop: stop
              });
            }
          }
          break;

        case '-webkit-radial-gradient':
        case '-moz-radial-gradient':
        case '-o-radial-gradient':

          gradient = {
            type: 'circle',
            x0: 0,
            y0: 0,
            x1: bounds.width,
            y1: bounds.height,
            cx: 0,
            cy: 0,
            rx: 0,
            ry: 0,
            colorStops: []
          };

          // center
          m2 = m1[2].match(/(\d{1,3})%?\s(\d{1,3})%?/);
          if(m2){
            gradient.cx = (m2[1] * bounds.width) / 100;
            gradient.cy = (m2[2] * bounds.height) / 100;
          }

          // size
          m2 = m1[3].match(/\w+/);
          m3 = m1[4].match(/[a-z\-]*/);
          if(m2 && m3){
            switch(m3[0]){
              case 'farthest-corner':
              case 'cover': // is equivalent to farthest-corner
              case '': // mozilla removes "cover" from definition :(
                tl = Math.sqrt(Math.pow(gradient.cx, 2) + Math.pow(gradient.cy, 2));
                tr = Math.sqrt(Math.pow(gradient.cx, 2) + Math.pow(gradient.y1 - gradient.cy, 2));
                br = Math.sqrt(Math.pow(gradient.x1 - gradient.cx, 2) + Math.pow(gradient.y1 - gradient.cy, 2));
                bl = Math.sqrt(Math.pow(gradient.x1 - gradient.cx, 2) + Math.pow(gradient.cy, 2));
                gradient.rx = gradient.ry = Math.max(tl, tr, br, bl);
                break;
              case 'closest-corner':
                tl = Math.sqrt(Math.pow(gradient.cx, 2) + Math.pow(gradient.cy, 2));
                tr = Math.sqrt(Math.pow(gradient.cx, 2) + Math.pow(gradient.y1 - gradient.cy, 2));
                br = Math.sqrt(Math.pow(gradient.x1 - gradient.cx, 2) + Math.pow(gradient.y1 - gradient.cy, 2));
                bl = Math.sqrt(Math.pow(gradient.x1 - gradient.cx, 2) + Math.pow(gradient.cy, 2));
                gradient.rx = gradient.ry = Math.min(tl, tr, br, bl);
                break;
              case 'farthest-side':
                if(m2[0] === 'circle'){
                  gradient.rx = gradient.ry = Math.max(
                    gradient.cx,
                    gradient.cy,
                    gradient.x1 - gradient.cx,
                    gradient.y1 - gradient.cy
                    );
                } else { // ellipse

                  gradient.type = m2[0];

                  gradient.rx = Math.max(
                    gradient.cx,
                    gradient.x1 - gradient.cx
                    );
                  gradient.ry = Math.max(
                    gradient.cy,
                    gradient.y1 - gradient.cy
                    );
                }
                break;
              case 'closest-side':
              case 'contain': // is equivalent to closest-side
                if(m2[0] === 'circle'){
                  gradient.rx = gradient.ry = Math.min(
                    gradient.cx,
                    gradient.cy,
                    gradient.x1 - gradient.cx,
                    gradient.y1 - gradient.cy
                    );
                } else { // ellipse

                  gradient.type = m2[0];

                  gradient.rx = Math.min(
                    gradient.cx,
                    gradient.x1 - gradient.cx
                    );
                  gradient.ry = Math.min(
                    gradient.cy,
                    gradient.y1 - gradient.cy
                    );
                }
                break;

            // TODO: add support for "30px 40px" sizes (webkit only)
            }
          }

          // color stops
          m2 = m1[5].match(/((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\)(?:\s\d{1,3}(?:%|px))?)+/g);
          if(m2){
            m2Len = m2.length;
            step = 1 / Math.max(m2Len - 1, 1);
            for(i = 0; i < m2Len; i+=1){
              m3 = m2[i].match(/((?:rgb|rgba)\(\d{1,3},\s\d{1,3},\s\d{1,3}(?:,\s[0-9\.]+)?\))\s*(\d{1,3})?(%|px)?/);
              if(m3[2]){
                stop = parseFloat(m3[2]);
                if(m3[3] === '%'){
                  stop /= 100;
                } else { // px - stupid opera
                  stop /= bounds.width;
                }
              } else {
                stop = i * step;
              }
              gradient.colorStops.push({
                color: m3[1],
                stop: stop
              });
            }
          }
          break;
      }
    }

    return gradient;
  };

  function addScrollStops(grad) {
    return function(colorStop) {
      try {
        grad.addColorStop(colorStop.stop, colorStop.color);
      }
      catch(e) {
        Util.log(['failed to add color stop: ', e, '; tried to add: ', colorStop]);
      }
    };
  }

  Generate.Gradient = function(src, bounds) {
    if(bounds.width === 0 || bounds.height === 0) {
      return;
    }

    var canvas = document.createElement('canvas'),
    ctx = canvas.getContext('2d'),
    gradient, grad;

    canvas.width = bounds.width;
    canvas.height = bounds.height;

    // TODO: add support for multi defined background gradients
    gradient = _html2canvas.Generate.parseGradient(src, bounds);

    if(gradient) {
      switch(gradient.type) {
        case 'linear':
          grad = ctx.createLinearGradient(gradient.x0, gradient.y0, gradient.x1, gradient.y1);
          gradient.colorStops.forEach(addScrollStops(grad));
          ctx.fillStyle = grad;
          ctx.fillRect(0, 0, bounds.width, bounds.height);
          break;

        case 'circle':
          grad = ctx.createRadialGradient(gradient.cx, gradient.cy, 0, gradient.cx, gradient.cy, gradient.rx);
          gradient.colorStops.forEach(addScrollStops(grad));
          ctx.fillStyle = grad;
          ctx.fillRect(0, 0, bounds.width, bounds.height);
          break;

        case 'ellipse':
          var canvasRadial = document.createElement('canvas'),
            ctxRadial = canvasRadial.getContext('2d'),
            ri = Math.max(gradient.rx, gradient.ry),
            di = ri * 2;

          canvasRadial.width = canvasRadial.height = di;

          grad = ctxRadial.createRadialGradient(gradient.rx, gradient.ry, 0, gradient.rx, gradient.ry, ri);
          gradient.colorStops.forEach(addScrollStops(grad));

          ctxRadial.fillStyle = grad;
          ctxRadial.fillRect(0, 0, di, di);

          ctx.fillStyle = gradient.colorStops[gradient.colorStops.length - 1].color;
          ctx.fillRect(0, 0, canvas.width, canvas.height);
          ctx.drawImage(canvasRadial, gradient.cx - gradient.rx, gradient.cy - gradient.ry, 2 * gradient.rx, 2 * gradient.ry);
          break;
      }
    }

    return canvas;
  };

  Generate.ListAlpha = function(number) {
    var tmp = "",
    modulus;

    do {
      modulus = number % 26;
      tmp = String.fromCharCode((modulus) + 64) + tmp;
      number = number / 26;
    }while((number*26) > 26);

    return tmp;
  };

  Generate.ListRoman = function(number) {
    var romanArray = ["M", "CM", "D", "CD", "C", "XC", "L", "XL", "X", "IX", "V", "IV", "I"],
    decimal = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1],
    roman = "",
    v,
    len = romanArray.length;

    if (number <= 0 || number >= 4000) {
      return number;
    }

    for (v=0; v < len; v+=1) {
      while (number >= decimal[v]) {
        number -= decimal[v];
        roman += romanArray[v];
      }
    }

    return roman;
  };
})();
function h2cRenderContext(width, height) {
  var storage = [];
  return {
    storage: storage,
    width: width,
    height: height,
    clip: function() {
      storage.push({
        type: "function",
        name: "clip",
        'arguments': arguments
      });
    },
    translate: function() {
      storage.push({
        type: "function",
        name: "translate",
        'arguments': arguments
      });
    },
    fill: function() {
      storage.push({
        type: "function",
        name: "fill",
        'arguments': arguments
      });
    },
    save: function() {
      storage.push({
        type: "function",
        name: "save",
        'arguments': arguments
      });
    },
    restore: function() {
      storage.push({
        type: "function",
        name: "restore",
        'arguments': arguments
      });
    },
    fillRect: function () {
      storage.push({
        type: "function",
        name: "fillRect",
        'arguments': arguments
      });
    },
    createPattern: function() {
      storage.push({
        type: "function",
        name: "createPattern",
        'arguments': arguments
      });
    },
    drawShape: function() {

      var shape = [];

      storage.push({
        type: "function",
        name: "drawShape",
        'arguments': shape
      });

      return {
        moveTo: function() {
          shape.push({
            name: "moveTo",
            'arguments': arguments
          });
        },
        lineTo: function() {
          shape.push({
            name: "lineTo",
            'arguments': arguments
          });
        },
        arcTo: function() {
          shape.push({
            name: "arcTo",
            'arguments': arguments
          });
        },
        bezierCurveTo: function() {
          shape.push({
            name: "bezierCurveTo",
            'arguments': arguments
          });
        },
        quadraticCurveTo: function() {
          shape.push({
            name: "quadraticCurveTo",
            'arguments': arguments
          });
        }
      };

    },
    drawImage: function () {
      storage.push({
        type: "function",
        name: "drawImage",
        'arguments': arguments
      });
    },
    fillText: function () {
      storage.push({
        type: "function",
        name: "fillText",
        'arguments': arguments
      });
    },
    setVariable: function (variable, value) {
      storage.push({
        type: "variable",
        name: variable,
        'arguments': value
      });
      return value;
    }
  };
}
_html2canvas.Parse = function (images, options, cb) {
  var scrollTop = $(window).scrollTop();
  window.scroll(0,0);

  var element = (( options.elements === undefined ) ? document.body : options.elements[0]), // select body by default
  numDraws = 0,
  doc = element.ownerDocument,
  Util = _html2canvas.Util,
  support = Util.Support(options, doc),
  ignoreElementsRegExp = new RegExp("(" + options.ignoreElements + ")"),
  body = doc.body,
  getCSS = Util.getCSS,
  pseudoHide = "___html2canvas___pseudoelement",
  hidePseudoElementsStyles = doc.createElement('style');

  hidePseudoElementsStyles.innerHTML = '.' + pseudoHide + 
  '-parent:before { content: "" !important; display: none !important; }' +
  '.' + pseudoHide + '-parent:after { content: "" !important; display: none !important; }';

  body.appendChild(hidePseudoElementsStyles);

  images = images || {};

  init();

  function init() {
    var background = getCSS(document.documentElement, "backgroundColor"),
      transparentBackground = (Util.isTransparent(background) && element === document.body),
      stack = renderElement(element, null, false, transparentBackground);

    // create pseudo elements in a single pass to prevent synchronous layouts
    addPseudoElements(element);
    
    parseChildren(element, stack, function() {
      if (transparentBackground) {
        background = stack.backgroundColor;
      }

      removePseudoElements();

      Util.log('Done parsing, moving to Render.');

      cb({
        backgroundColor: background,
        stack: stack
      });
    });
  }

  // Given a root element, find all pseudo elements below, create elements mocking pseudo element styles 
  // so we can process them as normal elements, and hide the original pseudo elements so they don't interfere 
  // with layout.
  function addPseudoElements(el) {
    // These are done in discrete steps to prevent a relayout loop caused by addClass() invalidating
    // layouts & getPseudoElement calling getComputedStyle.
    var jobs = [], classes = [];
    getPseudoElementClasses();
    findPseudoElements(el);
    runJobs();

    function getPseudoElementClasses(){
      var findPsuedoEls = /:before|:after/;
      var sheets = document.styleSheets;
      for (var i = 0, j = sheets.length; i < j; i++) {
        try {
          var rules = sheets[i].cssRules;
          for (var k = 0, l = rules.length; k < l; k++) {
            if(findPsuedoEls.test(rules[k].selectorText)) {
              classes.push(rules[k].selectorText);
            }
          }
        }
        catch(e) { // will throw security exception for style sheets loaded from external domains
        }
      }

      // Trim off the :after and :before (or ::after and ::before)
      for (i = 0, j = classes.length; i < j; i++) {
        classes[i] = classes[i].match(/(^[^:]*)/)[1];
      }

      var ind = classes.indexOf('*, ');
      while(ind != -1) {
        classes.splice(ind, 1);
        ind = classes.indexOf('*, ');
      }
    }

    // Using the list of elements we know how pseudo el styles, create fake pseudo elements.
    function findPseudoElements(el) {
      var els = document.querySelectorAll(classes.join(','));
      for(var i = 0, j = els.length; i < j; i++) {
        createPseudoElements(els[i]);
      }
    }

    // Create pseudo elements & add them to a job queue.
    function createPseudoElements(el) {
      var before = getPseudoElement(el, ':before'),
      after = getPseudoElement(el, ':after');

      if(before) {
        jobs.push({type: 'before', pseudo: before, el: el});
      }

      if (after) {
        jobs.push({type: 'after', pseudo: after, el: el});
      }
    }

    // Adds a class to the pseudo's parent to prevent the original before/after from messing
    // with layouts.
    // Execute the inserts & addClass() calls in a batch to prevent relayouts.
    function runJobs() {
      // Add Class
      jobs.forEach(function(job){
        addClass(job.el, pseudoHide + "-parent");
      });

      // Insert el
      jobs.forEach(function(job){
        if(job.type === 'before'){
          job.el.insertBefore(job.pseudo, job.el.firstChild);
        } else {
          job.el.appendChild(job.pseudo);
        }
      });
    }
  }



  // Delete our fake pseudo elements from the DOM. This will remove those actual elements
  // and the classes on their parents that hide the actual pseudo elements.
  // Note that NodeLists are 'live' collections so you can't use a for loop here. They are
  // actually deleted from the NodeList after each iteration.
  function removePseudoElements(){
    // delete pseudo elements
    body.removeChild(hidePseudoElementsStyles);
    var pseudos = document.getElementsByClassName(pseudoHide + "-element");
    while (pseudos.length) {
      pseudos[0].parentNode.removeChild(pseudos[0]);
    }

    // Remove pseudo hiding classes
    var parents = document.getElementsByClassName(pseudoHide + "-parent");
    while(parents.length) {
      removeClass(parents[0], pseudoHide + "-parent");
    }
  }

  function addClass (el, className) {
    if (el.classList) {
      el.classList.add(className);
    } else {
      el.className = el.className + " " + className;
    }
  }

  function removeClass (el, className) {
    if (el.classList) {
      el.classList.remove(className);
    } else {
      el.className = el.className.replace(className, "").trim();
    }
  }

  function hasClass (el, className) {
    return el.className.indexOf(className) > -1;
  }

  // Note that this doesn't work in < IE8, but we don't support that anyhow
  function nodeListToArray (nodeList) {
    return Array.prototype.slice.call(nodeList);  
  }

  function documentWidth () {
    return Math.max(
      Math.max(doc.body.scrollWidth, doc.documentElement.scrollWidth),
      Math.max(doc.body.offsetWidth, doc.documentElement.offsetWidth),
      Math.max(doc.body.clientWidth, doc.documentElement.clientWidth)
      );
  }

  function documentHeight () {
    return Math.max(
      Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight),
      Math.max(doc.body.offsetHeight, doc.documentElement.offsetHeight),
      Math.max(doc.body.clientHeight, doc.documentElement.clientHeight)
      );
  }

  function getCSSInt(element, attribute) {
    var val = parseInt(getCSS(element, attribute), 10);
    return (isNaN(val)) ? 0 : val; // borders in old IE are throwing 'medium' for demo.html
  }

  function renderRect (ctx, x, y, w, h, bgcolor) {
    if (bgcolor !== "transparent"){
      ctx.setVariable("fillStyle", bgcolor);
      ctx.fillRect(x, y, w, h);
      numDraws+=1;
    }
  }

  function capitalize(m, p1, p2) {
    if (m.length > 0) {
      return p1 + p2.toUpperCase();
    }
  }

  function textTransform (text, transform) {
    switch(transform){
      case "lowercase":
        return text.toLowerCase();
      case "capitalize":
        return text.replace( /(^|\s|:|-|\(|\))([a-z])/g, capitalize);
      case "uppercase":
        return text.toUpperCase();
      default:
        return text;
    }
  }

  function noLetterSpacing(letter_spacing) {
    return (/^(normal|none|0px)$/.test(letter_spacing));
  }

  function drawText(currentText, x, y, ctx){
    if (currentText !== null && Util.trimText(currentText).length > 0) {
      ctx.fillText(currentText, x, y);
      numDraws+=1;
    }
  }

  function setTextVariables(ctx, el, text_decoration, color) {
    var align = false,
    bold = getCSS(el, "fontWeight"),
    family = getCSS(el, "fontFamily"),
    size = getCSS(el, "fontSize"),
    shadows = Util.parseTextShadows(getCSS(el, "textShadow"));

    switch(parseInt(bold, 10)){
      case 401:
        bold = "bold";
        break;
      case 400:
        bold = "normal";
        break;
    }

    ctx.setVariable("fillStyle", color);
    ctx.setVariable("font", [getCSS(el, "fontStyle"), getCSS(el, "fontVariant"), bold, size, family].join(" "));
    ctx.setVariable("textAlign", (align) ? "right" : "left");

    if (shadows.length) {
      // TODO: support multiple text shadows
      // apply the first text shadow
      ctx.setVariable("shadowColor", shadows[0].color);
      ctx.setVariable("shadowOffsetX", shadows[0].offsetX);
      ctx.setVariable("shadowOffsetY", shadows[0].offsetY);
      ctx.setVariable("shadowBlur", shadows[0].blur);
    }

    if (text_decoration !== "none"){
      return Util.Font(family, size, doc);
    }
  }

  function renderTextDecoration(ctx, text_decoration, bounds, metrics, color) {
    switch(text_decoration) {
      case "underline":
        // Draws a line at the baseline of the font
        // TODO As some browsers display the line as more than 1px if the font-size is big, need to take that into account both in position and size
        renderRect(ctx, bounds.left, Math.round(bounds.top + metrics.baseline + metrics.lineWidth), bounds.width, 1, color);
        break;
      case "overline":
        renderRect(ctx, bounds.left, Math.round(bounds.top), bounds.width, 1, color);
        break;
      case "line-through":
        // TODO try and find exact position for line-through
        renderRect(ctx, bounds.left, Math.ceil(bounds.top + metrics.middle + metrics.lineWidth), bounds.width, 1, color);
        break;
    }
  }

  function getTextBounds(state, text, textDecoration, isLast, transform) {
    var bounds;
    if (support.rangeBounds && !transform) {
      if (textDecoration !== "none" || Util.trimText(text).length !== 0) {
        bounds = textRangeBounds(text, state.node, state.textOffset);
      }
      state.textOffset += text.length;
    } else if (state.node && typeof state.node.nodeValue === "string" ){
      var newTextNode = (isLast) ? state.node.splitText(text.length) : null;
      bounds = textWrapperBounds(state.node, transform);
      state.node = newTextNode;
    }
    return bounds;
  }

  function textRangeBounds(text, textNode, textOffset) {
    var range = doc.createRange();
    range.setStart(textNode, textOffset);
    range.setEnd(textNode, textOffset + text.length);
    return range.getBoundingClientRect();
  }

  function textWrapperBounds(oldTextNode, transform) {
    var parent = oldTextNode.parentNode,
    wrapElement = doc.createElement('wrapper'),
    backupText = oldTextNode.cloneNode(true);

    wrapElement.appendChild(oldTextNode.cloneNode(true));
    parent.replaceChild(wrapElement, oldTextNode);

    var bounds = transform ? Util.OffsetBounds(wrapElement) : Util.Bounds(wrapElement);
    parent.replaceChild(backupText, wrapElement);
    return bounds;
  }

  function renderText(el, textNode, stack) {
    var ctx = stack.ctx,
    color = getCSS(el, "color"),
    textDecoration = getCSS(el, "textDecoration"),
    textAlign = getCSS(el, "textAlign"),
    metrics,
    textList,
    state = {
      node: textNode,
      textOffset: 0
    };

    if (Util.trimText(textNode.nodeValue).length > 0) {
      textNode.nodeValue = textTransform(textNode.nodeValue, getCSS(el, "textTransform"));
      textAlign = textAlign.replace(["-webkit-auto"],["auto"]);

      textList = (!options.letterRendering && /^(left|right|justify|auto)$/.test(textAlign) && noLetterSpacing(getCSS(el, "letterSpacing"))) ?
      textNode.nodeValue.split(/(\b| )/)
      : textNode.nodeValue.split("");

      metrics = setTextVariables(ctx, el, textDecoration, color);

      if (options.chinese) {
        textList.forEach(function(word, index) {
          if (/.*[\u4E00-\u9FA5].*$/.test(word)) {
            word = word.split("");
            word.unshift(index, 1);
            textList.splice.apply(textList, word);
          }
        });
      }

      textList.forEach(function(text, index) {
        var bounds = getTextBounds(state, text, textDecoration, (index < textList.length - 1), stack.transform.matrix);
        if (bounds) {
          drawText(text, bounds.left, bounds.bottom, ctx);
          renderTextDecoration(ctx, textDecoration, bounds, metrics, color);
        }
      });
    }
  }

  function listPosition (element, val) {
    var boundElement = doc.createElement( "boundelement" ),
    originalType,
    bounds;

    boundElement.style.display = "inline";

    originalType = element.style.listStyleType;
    element.style.listStyleType = "none";

    boundElement.appendChild(doc.createTextNode(val));

    element.insertBefore(boundElement, element.firstChild);

    bounds = Util.Bounds(boundElement);
    element.removeChild(boundElement);
    element.style.listStyleType = originalType;
    return bounds;
  }

  function elementIndex(el) {
    var i = -1,
    count = 1,
    childs = el.parentNode.childNodes;

    if (el.parentNode) {
      while(childs[++i] !== el) {
        if (childs[i].nodeType === 1) {
          count++;
        }
      }
      return count;
    } else {
      return -1;
    }
  }

  function listItemText(element, type) {
    var currentIndex = elementIndex(element), text;
    switch(type){
      case "decimal":
        text = currentIndex;
        break;
      case "decimal-leading-zero":
        text = (currentIndex.toString().length === 1) ? currentIndex = "0" + currentIndex.toString() : currentIndex.toString();
        break;
      case "upper-roman":
        text = _html2canvas.Generate.ListRoman( currentIndex );
        break;
      case "lower-roman":
        text = _html2canvas.Generate.ListRoman( currentIndex ).toLowerCase();
        break;
      case "lower-alpha":
        text = _html2canvas.Generate.ListAlpha( currentIndex ).toLowerCase();
        break;
      case "upper-alpha":
        text = _html2canvas.Generate.ListAlpha( currentIndex );
        break;
    }

    return text + ". ";
  }

  function renderListItem(element, stack, elBounds) {
    var x,
    text,
    ctx = stack.ctx,
    type = getCSS(element, "listStyleType"),
    listBounds;

    if (/^(decimal|decimal-leading-zero|upper-alpha|upper-latin|upper-roman|lower-alpha|lower-greek|lower-latin|lower-roman)$/i.test(type)) {
      text = listItemText(element, type);
      listBounds = listPosition(element, text);
      setTextVariables(ctx, element, "none", getCSS(element, "color"));

      if (getCSS(element, "listStylePosition") === "inside") {
        ctx.setVariable("textAlign", "left");
        x = elBounds.left;
      } else {
        return;
      }

      drawText(text, x, listBounds.bottom, ctx);
    }
  }

  function loadImage (src){
    var img = images[src];
    return (img && img.succeeded === true) ? img.img : false;
  }

  function clipBounds(src, dst){
    var x = Math.max(src.left, dst.left),
    y = Math.max(src.top, dst.top),
    x2 = Math.min((src.left + src.width), (dst.left + dst.width)),
    y2 = Math.min((src.top + src.height), (dst.top + dst.height));

    return {
      left:x,
      top:y,
      width:x2-x,
      height:y2-y
    };
  }

  function setZ(element, stack, parentStack){
    var newContext,
    isPositioned = stack.cssPosition !== 'static',
    zIndex = isPositioned ? getCSS(element, 'zIndex') : 'auto',
    opacity = getCSS(element, 'opacity'),
    isFloated = getCSS(element, 'cssFloat') !== 'none';

    // https://developer.mozilla.org/en-US/docs/Web/Guide/CSS/Understanding_z_index/The_stacking_context
    // When a new stacking context should be created:
    // the root element (HTML),
    // positioned (absolutely or relatively) with a z-index value other than "auto",
    // elements with an opacity value less than 1. (See the specification for opacity),
    // on mobile WebKit and Chrome 22+, position: fixed always creates a new stacking context, even when z-index is "auto" (See this post)

    stack.zIndex = newContext = h2czContext(zIndex);
    newContext.isPositioned = isPositioned;
    newContext.isFloated = isFloated;
    newContext.opacity = opacity;
    newContext.ownStacking = (zIndex !== 'auto' || opacity < 1);
    newContext.depth = parentStack ? (parentStack.zIndex.depth + 1) : 0;

    if (parentStack) {
      parentStack.zIndex.children.push(stack);
    }
  }

  function h2czContext(zindex) {
    return {
      depth: 0,
      zindex: zindex,
      children: []
    };
  }

  function renderImage(ctx, element, image, bounds, borders) {

    var paddingLeft = getCSSInt(element, 'paddingLeft'),
    paddingTop = getCSSInt(element, 'paddingTop'),
    paddingRight = getCSSInt(element, 'paddingRight'),
    paddingBottom = getCSSInt(element, 'paddingBottom');

    drawImage(
      ctx,
      image,
      0, //sx
      0, //sy
      image.width, //sw
      image.height, //sh
      bounds.left + paddingLeft + borders[3].width, //dx
      bounds.top + paddingTop + borders[0].width, // dy
      bounds.width - (borders[1].width + borders[3].width + paddingLeft + paddingRight), //dw
      bounds.height - (borders[0].width + borders[2].width + paddingTop + paddingBottom) //dh
      );
  }

  function getBorderData(element) {
    return ["Top", "Right", "Bottom", "Left"].map(function(side) {
      return {
        width: getCSSInt(element, 'border' + side + 'Width'),
        color: getCSS(element, 'border' + side + 'Color')
      };
    });
  }

  function getBorderRadiusData(element) {
    return ["TopLeft", "TopRight", "BottomRight", "BottomLeft"].map(function(side) {
      return getCSS(element, 'border' + side + 'Radius');
    });
  }

  function getCurvePoints(x, y, r1, r2) {
    var kappa = 4 * ((Math.sqrt(2) - 1) / 3);
    var ox = (r1) * kappa, // control point offset horizontal
    oy = (r2) * kappa, // control point offset vertical
    xm = x + r1, // x-middle
    ym = y + r2; // y-middle
    return {
      topLeft: bezierCurve({
        x:x,
        y:ym
      }, {
        x:x,
        y:ym - oy
      }, {
        x:xm - ox,
        y:y
      }, {
        x:xm,
        y:y
      }),
      topRight: bezierCurve({
        x:x,
        y:y
      }, {
        x:x + ox,
        y:y
      }, {
        x:xm,
        y:ym - oy
      }, {
        x:xm,
        y:ym
      }),
      bottomRight: bezierCurve({
        x:xm,
        y:y
      }, {
        x:xm,
        y:y + oy
      }, {
        x:x + ox,
        y:ym
      }, {
        x:x,
        y:ym
      }),
      bottomLeft: bezierCurve({
        x:xm,
        y:ym
      }, {
        x:xm - ox,
        y:ym
      }, {
        x:x,
        y:y + oy
      }, {
        x:x,
        y:y
      })
    };
  }

  function bezierCurve(start, startControl, endControl, end) {

    var lerp = function (a, b, t) {
      return {
        x:a.x + (b.x - a.x) * t,
        y:a.y + (b.y - a.y) * t
      };
    };

    return {
      start: start,
      startControl: startControl,
      endControl: endControl,
      end: end,
      subdivide: function(t) {
        var ab = lerp(start, startControl, t),
        bc = lerp(startControl, endControl, t),
        cd = lerp(endControl, end, t),
        abbc = lerp(ab, bc, t),
        bccd = lerp(bc, cd, t),
        dest = lerp(abbc, bccd, t);
        return [bezierCurve(start, ab, abbc, dest), bezierCurve(dest, bccd, cd, end)];
      },
      curveTo: function(borderArgs) {
        borderArgs.push(["bezierCurve", startControl.x, startControl.y, endControl.x, endControl.y, end.x, end.y]);
      },
      curveToReversed: function(borderArgs) {
        borderArgs.push(["bezierCurve", endControl.x, endControl.y, startControl.x, startControl.y, start.x, start.y]);
      }
    };
  }

  function parseCorner(borderArgs, radius1, radius2, corner1, corner2, x, y) {
    if (radius1[0] > 0 || radius1[1] > 0) {
      borderArgs.push(["line", corner1[0].start.x, corner1[0].start.y]);
      corner1[0].curveTo(borderArgs);
      corner1[1].curveTo(borderArgs);
    } else {
      borderArgs.push(["line", x, y]);
    }

    if (radius2[0] > 0 || radius2[1] > 0) {
      borderArgs.push(["line", corner2[0].start.x, corner2[0].start.y]);
    }
  }

  function drawSide(borderData, radius1, radius2, outer1, inner1, outer2, inner2) {
    var borderArgs = [];

    if (radius1[0] > 0 || radius1[1] > 0) {
      borderArgs.push(["line", outer1[1].start.x, outer1[1].start.y]);
      outer1[1].curveTo(borderArgs);
    } else {
      borderArgs.push([ "line", borderData.c1[0], borderData.c1[1]]);
    }

    if (radius2[0] > 0 || radius2[1] > 0) {
      borderArgs.push(["line", outer2[0].start.x, outer2[0].start.y]);
      outer2[0].curveTo(borderArgs);
      borderArgs.push(["line", inner2[0].end.x, inner2[0].end.y]);
      inner2[0].curveToReversed(borderArgs);
    } else {
      borderArgs.push([ "line", borderData.c2[0], borderData.c2[1]]);
      borderArgs.push([ "line", borderData.c3[0], borderData.c3[1]]);
    }

    if (radius1[0] > 0 || radius1[1] > 0) {
      borderArgs.push(["line", inner1[1].end.x, inner1[1].end.y]);
      inner1[1].curveToReversed(borderArgs);
    } else {
      borderArgs.push([ "line", borderData.c4[0], borderData.c4[1]]);
    }

    return borderArgs;
  }

  function calculateCurvePoints(bounds, borderRadius, borders) {

    var x = bounds.left,
    y = bounds.top,
    width = bounds.width,
    height = bounds.height,

    tlh = borderRadius[0][0],
    tlv = borderRadius[0][1],
    trh = borderRadius[1][0],
    trv = borderRadius[1][1],
    brh = borderRadius[2][0],
    brv = borderRadius[2][1],
    blh = borderRadius[3][0],
    blv = borderRadius[3][1],

    topWidth = width - trh,
    rightHeight = height - brv,
    bottomWidth = width - brh,
    leftHeight = height - blv;

    return {
      topLeftOuter: getCurvePoints(
        x,
        y,
        tlh,
        tlv
        ).topLeft.subdivide(0.5),

      topLeftInner: getCurvePoints(
        x + borders[3].width,
        y + borders[0].width,
        Math.max(0, tlh - borders[3].width),
        Math.max(0, tlv - borders[0].width)
        ).topLeft.subdivide(0.5),

      topRightOuter: getCurvePoints(
        x + topWidth,
        y,
        trh,
        trv
        ).topRight.subdivide(0.5),

      topRightInner: getCurvePoints(
        x + Math.min(topWidth, width + borders[3].width),
        y + borders[0].width,
        (topWidth > width + borders[3].width) ? 0 :trh - borders[3].width,
        trv - borders[0].width
        ).topRight.subdivide(0.5),

      bottomRightOuter: getCurvePoints(
        x + bottomWidth,
        y + rightHeight,
        brh,
        brv
        ).bottomRight.subdivide(0.5),

      bottomRightInner: getCurvePoints(
        x + Math.min(bottomWidth, width + borders[3].width),
        y + Math.min(rightHeight, height + borders[0].width),
        Math.max(0, brh - borders[1].width),
        Math.max(0, brv - borders[2].width)
        ).bottomRight.subdivide(0.5),

      bottomLeftOuter: getCurvePoints(
        x,
        y + leftHeight,
        blh,
        blv
        ).bottomLeft.subdivide(0.5),

      bottomLeftInner: getCurvePoints(
        x + borders[3].width,
        y + leftHeight,
        Math.max(0, blh - borders[3].width),
        Math.max(0, blv - borders[2].width)
        ).bottomLeft.subdivide(0.5)
    };
  }

  function getBorderClip(element, borderPoints, borders, radius, bounds) {
    var backgroundClip = getCSS(element, 'backgroundClip'),
    borderArgs = [];

    switch(backgroundClip) {
      case "content-box":
      case "padding-box":
        parseCorner(borderArgs, radius[0], radius[1], borderPoints.topLeftInner, borderPoints.topRightInner, bounds.left + borders[3].width, bounds.top + borders[0].width);
        parseCorner(borderArgs, radius[1], radius[2], borderPoints.topRightInner, borderPoints.bottomRightInner, bounds.left + bounds.width - borders[1].width, bounds.top + borders[0].width);
        parseCorner(borderArgs, radius[2], radius[3], borderPoints.bottomRightInner, borderPoints.bottomLeftInner, bounds.left + bounds.width - borders[1].width, bounds.top + bounds.height - borders[2].width);
        parseCorner(borderArgs, radius[3], radius[0], borderPoints.bottomLeftInner, borderPoints.topLeftInner, bounds.left + borders[3].width, bounds.top + bounds.height - borders[2].width);
        break;

      default:
        parseCorner(borderArgs, radius[0], radius[1], borderPoints.topLeftOuter, borderPoints.topRightOuter, bounds.left, bounds.top);
        parseCorner(borderArgs, radius[1], radius[2], borderPoints.topRightOuter, borderPoints.bottomRightOuter, bounds.left + bounds.width, bounds.top);
        parseCorner(borderArgs, radius[2], radius[3], borderPoints.bottomRightOuter, borderPoints.bottomLeftOuter, bounds.left + bounds.width, bounds.top + bounds.height);
        parseCorner(borderArgs, radius[3], radius[0], borderPoints.bottomLeftOuter, borderPoints.topLeftOuter, bounds.left, bounds.top + bounds.height);
        break;
    }

    return borderArgs;
  }

  function parseBorders(element, bounds, borders){
    var x = bounds.left,
    y = bounds.top,
    width = bounds.width,
    height = bounds.height,
    borderSide,
    bx,
    by,
    bw,
    bh,
    borderArgs,
    // http://www.w3.org/TR/css3-background/#the-border-radius
    borderRadius = getBorderRadiusData(element),
    borderPoints = calculateCurvePoints(bounds, borderRadius, borders),
    borderData = {
      clip: getBorderClip(element, borderPoints, borders, borderRadius, bounds),
      borders: []
    };

    for (borderSide = 0; borderSide < 4; borderSide++) {

      if (borders[borderSide].width > 0) {
        bx = x;
        by = y;
        bw = width;
        bh = height - (borders[2].width);

        switch(borderSide) {
          case 0:
            // top border
            bh = borders[0].width;

            borderArgs = drawSide({
              c1: [bx, by],
              c2: [bx + bw, by],
              c3: [bx + bw - borders[1].width, by + bh],
              c4: [bx + borders[3].width, by + bh]
            }, borderRadius[0], borderRadius[1],
            borderPoints.topLeftOuter, borderPoints.topLeftInner, borderPoints.topRightOuter, borderPoints.topRightInner);
            break;
          case 1:
            // right border
            bx = x + width - (borders[1].width);
            bw = borders[1].width;

            borderArgs = drawSide({
              c1: [bx + bw, by],
              c2: [bx + bw, by + bh + borders[2].width],
              c3: [bx, by + bh],
              c4: [bx, by + borders[0].width]
            }, borderRadius[1], borderRadius[2],
            borderPoints.topRightOuter, borderPoints.topRightInner, borderPoints.bottomRightOuter, borderPoints.bottomRightInner);
            break;
          case 2:
            // bottom border
            by = (by + height) - (borders[2].width);
            bh = borders[2].width;

            borderArgs = drawSide({
              c1: [bx + bw, by + bh],
              c2: [bx, by + bh],
              c3: [bx + borders[3].width, by],
              c4: [bx + bw - borders[3].width, by]
            }, borderRadius[2], borderRadius[3],
            borderPoints.bottomRightOuter, borderPoints.bottomRightInner, borderPoints.bottomLeftOuter, borderPoints.bottomLeftInner);
            break;
          case 3:
            // left border
            bw = borders[3].width;

            borderArgs = drawSide({
              c1: [bx, by + bh + borders[2].width],
              c2: [bx, by],
              c3: [bx + bw, by + borders[0].width],
              c4: [bx + bw, by + bh]
            }, borderRadius[3], borderRadius[0],
            borderPoints.bottomLeftOuter, borderPoints.bottomLeftInner, borderPoints.topLeftOuter, borderPoints.topLeftInner);
            break;
        }

        borderData.borders.push({
          args: borderArgs,
          color: borders[borderSide].color
        });

      }
    }

    return borderData;
  }

  function createShape(ctx, args) {
    var shape = ctx.drawShape();
    args.forEach(function(border, index) {
      shape[(index === 0) ? "moveTo" : border[0] + "To" ].apply(null, border.slice(1));
    });
    return shape;
  }

  function renderBorders(ctx, borderArgs, color) {
    if (color !== "transparent") {
      ctx.setVariable( "fillStyle", color);
      createShape(ctx, borderArgs);
      ctx.fill();
      numDraws+=1;
    }
  }

  function renderFormValue (el, bounds, stack){

    var valueWrap = doc.createElement('valuewrap'),
    cssPropertyArray = ['lineHeight','textAlign','fontFamily','color','fontSize','paddingLeft','paddingTop','width','height','border','borderLeftWidth','borderTopWidth'],
    textValue,
    textNode;

    cssPropertyArray.forEach(function(property) {
      try {
        valueWrap.style[property] = getCSS(el, property);
      } catch(e) {
        // Older IE has issues with "border"
        Util.log("html2canvas: Parse: Exception caught in renderFormValue: " + e.message);
      }
    });

    valueWrap.style.borderColor = "black";
    valueWrap.style.borderStyle = "solid";
    valueWrap.style.display = "block";
    valueWrap.style.position = "absolute";

    if (/^(submit|reset|button|text|password)$/.test(el.type) || el.nodeName === "SELECT"){
      valueWrap.style.lineHeight = getCSS(el, "height");
    }

    valueWrap.style.top = bounds.top + "px";
    valueWrap.style.left = bounds.left + "px";

    textValue = (el.nodeName === "SELECT") ? (el.options[el.selectedIndex] || 0).text : el.value;
    if(!textValue) {
      textValue = el.placeholder;
    }

    textNode = doc.createTextNode(textValue);

    valueWrap.appendChild(textNode);
    body.appendChild(valueWrap);

    renderText(el, textNode, stack);
    body.removeChild(valueWrap);
  }

  function drawImage (ctx) {
    ctx.drawImage.apply(ctx, Array.prototype.slice.call(arguments, 1));
    numDraws+=1;
  }

  function getPseudoElement(el, which) {
    var elStyle = window.getComputedStyle(el, which);
    var parentStyle = window.getComputedStyle(el);
    // If no content attribute is present, the pseudo element is hidden,
    // or the parent has a content property equal to the content on the pseudo element,
    // move along. 
    if(!elStyle || !elStyle.content || elStyle.content === "none" || elStyle.content === "-moz-alt-content" || 
       elStyle.display === "none" || parentStyle.content === elStyle.content) {
      return;
    }
    var content = elStyle.content + '';

    // Strip inner quotes
    if(content[0] === "'" || content[0] === "\"") {
      content = content.replace(/(^['"])|(['"]$)/g, '');
    }

    var isImage = content.substr( 0, 3 ) === 'url',
    elps = document.createElement( isImage ? 'img' : 'span' );

    elps.className = pseudoHide + "-element ";

    Object.keys(elStyle).filter(indexedProperty).forEach(function(prop) {
      // Prevent assigning of read only CSS Rules, ex. length, parentRule
      try {
        elps.style[prop] = elStyle[prop];
      } catch (e) {
        Util.log(['Tried to assign readonly property ', prop, 'Error:', e]);
      }
    });

    if(isImage) {
      elps.src = Util.parseBackgroundImage(content)[0].args[0];
    } else {
      elps.innerHTML = content;
    }
    return elps;
  }

  function indexedProperty(property) {
    return (isNaN(window.parseInt(property, 10)));
  }

  function renderBackgroundRepeat(ctx, image, backgroundPosition, bounds) {
    var offsetX = Math.round(bounds.left + backgroundPosition.left),
    offsetY = Math.round(bounds.top + backgroundPosition.top);

    ctx.createPattern(image);
    ctx.translate(offsetX, offsetY);
    ctx.fill();
    ctx.translate(-offsetX, -offsetY);
  }

  function backgroundRepeatShape(ctx, image, backgroundPosition, bounds, left, top, width, height) {
    var args = [];
    args.push(["line", Math.round(left), Math.round(top)]);
    args.push(["line", Math.round(left + width), Math.round(top)]);
    args.push(["line", Math.round(left + width), Math.round(height + top)]);
    args.push(["line", Math.round(left), Math.round(height + top)]);
    createShape(ctx, args);
    ctx.save();
    ctx.clip();
    renderBackgroundRepeat(ctx, image, backgroundPosition, bounds);
    ctx.restore();
  }

  function renderBackgroundColor(ctx, backgroundBounds, bgcolor) {
    renderRect(
      ctx,
      backgroundBounds.left,
      backgroundBounds.top,
      backgroundBounds.width,
      backgroundBounds.height,
      bgcolor
      );
  }

  function renderBackgroundRepeating(el, bounds, ctx, image, imageIndex) {
    var backgroundSize = Util.BackgroundSize(el, bounds, image, imageIndex),
    backgroundPosition = Util.BackgroundPosition(el, bounds, image, imageIndex, backgroundSize),
    backgroundRepeat = getCSS(el, "backgroundRepeat").split(",").map(Util.trimText);

    image = resizeImage(image, backgroundSize);

    backgroundRepeat = backgroundRepeat[imageIndex] || backgroundRepeat[0];

    switch (backgroundRepeat) {
      case "repeat-x":
        backgroundRepeatShape(ctx, image, backgroundPosition, bounds,
          bounds.left, bounds.top + backgroundPosition.top, 99999, image.height);
        break;

      case "repeat-y":
        backgroundRepeatShape(ctx, image, backgroundPosition, bounds,
          bounds.left + backgroundPosition.left, bounds.top, image.width, 99999);
        break;

      case "no-repeat":
        backgroundRepeatShape(ctx, image, backgroundPosition, bounds,
          bounds.left + backgroundPosition.left, bounds.top + backgroundPosition.top, image.width, image.height);
        break;

      default:
        renderBackgroundRepeat(ctx, image, backgroundPosition, {
          top: bounds.top,
          left: bounds.left,
          width: image.width,
          height: image.height
        });
        break;
    }
  }

  function renderBackgroundImage(element, bounds, ctx) {
    var backgroundImage = getCSS(element, "backgroundImage"),
    backgroundImages = Util.parseBackgroundImage(backgroundImage),
    image,
    imageIndex = backgroundImages.length;

    while(imageIndex--) {
      backgroundImage = backgroundImages[imageIndex];

      if (!backgroundImage.args || backgroundImage.args.length === 0) {
        continue;
      }

      var key = backgroundImage.method === 'url' ?
      backgroundImage.args[0] :
      backgroundImage.value;

      image = loadImage(key);

      // TODO add support for background-origin
      if (image) {
        renderBackgroundRepeating(element, bounds, ctx, image, imageIndex);
      } else {
        Util.log("html2canvas: Error loading background:", backgroundImage);
      }
    }
  }

  function resizeImage(image, bounds) {
    if(image.width === bounds.width && image.height === bounds.height) {
      return image;
    }

    var ctx, canvas = doc.createElement('canvas');
    canvas.width = bounds.width;
    canvas.height = bounds.height;
    ctx = canvas.getContext("2d");
    drawImage(ctx, image, 0, 0, image.width, image.height, 0, 0, bounds.width, bounds.height );
    return canvas;
  }

  function setOpacity(ctx, element, parentStack) {
    return ctx.setVariable("globalAlpha", getCSS(element, "opacity") * ((parentStack) ? parentStack.opacity : 1));
  }

  function removePx(str) {
    return str.replace("px", "");
  }

  function getTransform(element, parentStack) {
    var transformRegExp = /(matrix)\((.+)\)/;
    var transform = getCSS(element, "transform") || getCSS(element, "-webkit-transform") || getCSS(element, "-moz-transform") || getCSS(element, "-ms-transform") || getCSS(element, "-o-transform");
    var transformOrigin = getCSS(element, "transform-origin") || getCSS(element, "-webkit-transform-origin") || getCSS(element, "-moz-transform-origin") || getCSS(element, "-ms-transform-origin") || getCSS(element, "-o-transform-origin") || "0px 0px";

    transformOrigin = transformOrigin.split(" ").map(removePx).map(Util.asFloat);

    var matrix;
    if (transform && transform !== "none") {
      var match = transform.match(transformRegExp);
      if (match) {
        switch(match[1]) {
          case "matrix":
            matrix = match[2].split(",").map(Util.trimText).map(Util.asFloat);
            break;
        }
      }
    }

    return {
      origin: transformOrigin,
      matrix: matrix
    };
  }

  function createStack(element, parentStack, bounds, transform) {
    var ctx = h2cRenderContext((!parentStack) ? documentWidth() : bounds.width , (!parentStack) ? documentHeight() : bounds.height),
    stack = {
      ctx: ctx,
      opacity: setOpacity(ctx, element, parentStack),
      cssPosition: getCSS(element, "position"),
      borders: getBorderData(element),
      transform: transform,
      clip: (parentStack && parentStack.clip) ? Util.Extend( {}, parentStack.clip ) : null
    };

    setZ(element, stack, parentStack);

    // TODO correct overflow for absolute content residing under a static position
    if (options.useOverflow === true && /(hidden|scroll|auto)/.test(getCSS(element, "overflow")) === true && /(BODY)/i.test(element.nodeName) === false){
      stack.clip = (stack.clip) ? clipBounds(stack.clip, bounds) : bounds;
    }

    return stack;
  }

  function getBackgroundBounds(borders, bounds, clip) {
    var backgroundBounds = {
      left: bounds.left + borders[3].width,
      top: bounds.top + borders[0].width,
      width: bounds.width - (borders[1].width + borders[3].width),
      height: bounds.height - (borders[0].width + borders[2].width)
    };

    if (clip) {
      backgroundBounds = clipBounds(backgroundBounds, clip);
    }

    return backgroundBounds;
  }

  function getBounds(element, transform) {
    var bounds = (transform.matrix) ? Util.OffsetBounds(element) : Util.Bounds(element);
    transform.origin[0] += bounds.left;
    transform.origin[1] += bounds.top;
    return bounds;
  }

  function renderElement(element, parentStack, ignoreBackground) {
    var transform = getTransform(element, parentStack),
    bounds = getBounds(element, transform),
    image,
    stack = createStack(element, parentStack, bounds, transform),
    borders = stack.borders,
    ctx = stack.ctx,
    backgroundBounds = getBackgroundBounds(borders, bounds, stack.clip),
    borderData = parseBorders(element, bounds, borders),
    backgroundColor = (ignoreElementsRegExp.test(element.nodeName)) ? "#efefef" : getCSS(element, "backgroundColor");


    createShape(ctx, borderData.clip);

    ctx.save();
    ctx.clip();

    if (backgroundBounds.height > 0 && backgroundBounds.width > 0 && !ignoreBackground) {
      renderBackgroundColor(ctx, bounds, backgroundColor);
      renderBackgroundImage(element, backgroundBounds, ctx);
    } else if (ignoreBackground) {
      stack.backgroundColor =  backgroundColor;
    }

    ctx.restore();

    borderData.borders.forEach(function(border) {
      renderBorders(ctx, border.args, border.color);
    });

    switch(element.nodeName){
      case "IMG":
        if ((image = loadImage(element.getAttribute('src')))) {
          renderImage(ctx, element, image, bounds, borders);
        } else {
          Util.log("html2canvas: Error loading <img>:" + element.getAttribute('src'));
        }
        break;
      case "INPUT":
        // TODO add all relevant type's, i.e. HTML5 new stuff
        // todo add support for placeholder attribute for browsers which support it
        if (/^(text|url|email|submit|button|reset)$/.test(element.type) && (element.value || element.placeholder || "").length > 0){
          renderFormValue(element, bounds, stack);
        }
        break;
      case "TEXTAREA":
        if ((element.value || element.placeholder || "").length > 0){
          renderFormValue(element, bounds, stack);
        }
        break;
      case "SELECT":
        if ((element.options||element.placeholder || "").length > 0){
          renderFormValue(element, bounds, stack);
        }
        break;
      case "LI":
        renderListItem(element, stack, backgroundBounds);
        break;
      case "CANVAS":
        renderImage(ctx, element, element, bounds, borders);
        break;
    }

    return stack;
  }

  function isElementVisible(element) {
    return (getCSS(element, 'display') !== "none" && getCSS(element, 'visibility') !== "hidden" && !element.hasAttribute("data-html2canvas-ignore"));
  }

  function parseElement (element, stack, cb) {
    if (!cb) {
      cb = function(){};
    }
    if (isElementVisible(element)) {
      stack = renderElement(element, stack, false) || stack;
      if (!ignoreElementsRegExp.test(element.nodeName)) {
        return parseChildren(element, stack, cb);
      }
    }
    cb();
  }

  function parseChildren(element, stack, cb) {
    var children = Util.Children(element);
    // After all nodes have processed, finished() will call the cb.
    // We add one and kick it off so this will still work when children.length === 0.
    // Note that unless async is true, this will happen synchronously, just will callbacks.
    var jobs = children.length + 1;
    finished(); 

    if (options.async) {
      children.forEach(function(node) {
        // Don't block the page from rendering
        setTimeout(function(){ parseNode(node); }, 0);
      });
    } else {
      children.forEach(parseNode);
    }

    function parseNode(node) {
      if (node.nodeType === node.ELEMENT_NODE) {
        parseElement(node, stack, finished);
      } else if (node.nodeType === node.TEXT_NODE) {
        renderText(element, node, stack);
        finished();
      } else {
        finished();
      }
    }
    function finished(el) {
      if (--jobs <= 0){
        Util.log("finished rendering " + children.length + " children.");
        cb();
      }
    }
  }
  window.scroll(0, scrollTop);
};
_html2canvas.Preload = function( options ) {

  var images = {
    numLoaded: 0,   // also failed are counted here
    numFailed: 0,
    numTotal: 0,
    cleanupDone: false
  },
  pageOrigin,
  Util = _html2canvas.Util,
  methods,
  i,
  count = 0,
  element = options.elements[0] || document.body,
  doc = element.ownerDocument,
  domImages = element.getElementsByTagName('img'), // Fetch images of the present element only
  imgLen = domImages.length,
  link = doc.createElement("a"),
  supportCORS = (function( img ){
    return (img.crossOrigin !== undefined);
  })(new Image()),
  timeoutTimer;

  link.href = window.location.href;
  pageOrigin  = link.protocol + link.host;

  function isSameOrigin(url){
    link.href = url;
    link.href = link.href; // YES, BELIEVE IT OR NOT, that is required for IE9 - http://jsfiddle.net/niklasvh/2e48b/
    var origin = link.protocol + link.host;
    return (origin === pageOrigin);
  }

  function start(){
    Util.log("html2canvas: start: images: " + images.numLoaded + " / " + images.numTotal + " (failed: " + images.numFailed + ")");
    if (!images.firstRun && images.numLoaded >= images.numTotal){
      Util.log("Finished loading images: # " + images.numTotal + " (failed: " + images.numFailed + ")");

      if (typeof options.complete === "function"){
        options.complete(images);
      }

    }
  }

  // TODO modify proxy to serve images with CORS enabled, where available
  function proxyGetImage(url, img, imageObj){
    var callback_name,
    scriptUrl = options.proxy,
    script;

    link.href = url;
    url = link.href; // work around for pages with base href="" set - WARNING: this may change the url

    callback_name = 'html2canvas_' + (count++);
    imageObj.callbackname = callback_name;

    if (scriptUrl.indexOf("?") > -1) {
      scriptUrl += "&";
    } else {
      scriptUrl += "?";
    }
    scriptUrl += 'url=' + encodeURIComponent(url) + '&callback=' + callback_name;
    script = doc.createElement("script");

    window[callback_name] = function(a){
      if (a.substring(0,6) === "error:"){
        imageObj.succeeded = false;
        images.numLoaded++;
        images.numFailed++;
        start();
      } else {
        setImageLoadHandlers(img, imageObj);
        img.src = a;
      }
      window[callback_name] = undefined; // to work with IE<9  // NOTE: that the undefined callback property-name still exists on the window object (for IE<9)
      try {
        delete window[callback_name];  // for all browser that support this
      } catch(ex) {}
      script.parentNode.removeChild(script);
      script = null;
      delete imageObj.script;
      delete imageObj.callbackname;
    };

    script.setAttribute("type", "text/javascript");
    script.setAttribute("src", scriptUrl);
    imageObj.script = script;
    window.document.body.appendChild(script);

  }

  function loadPseudoElement(element, type) {
    var style = window.getComputedStyle(element, type),
    content = style.content;
    if (content.substr(0, 3) === 'url') {
      methods.loadImage(_html2canvas.Util.parseBackgroundImage(content)[0].args[0]);
    }
    loadBackgroundImages(style.backgroundImage, element);
  }

  function loadPseudoElementImages(element) {
    loadPseudoElement(element, ":before");
    loadPseudoElement(element, ":after");
  }

  function loadGradientImage(backgroundImage, bounds) {
    var img = _html2canvas.Generate.Gradient(backgroundImage, bounds);

    if (img !== undefined){
      images[backgroundImage] = {
        img: img,
        succeeded: true
      };
      images.numTotal++;
      images.numLoaded++;
      start();
    }
  }

  function invalidBackgrounds(background_image) {
    return (background_image && background_image.method && background_image.args && background_image.args.length > 0 );
  }

  function loadBackgroundImages(background_image, el) {
    var bounds;

    _html2canvas.Util.parseBackgroundImage(background_image).filter(invalidBackgrounds).forEach(function(background_image) {
      if (background_image.method === 'url') {
        methods.loadImage(background_image.args[0]);
      } else if(background_image.method.match(/\-?gradient$/)) {
        if(bounds === undefined) {
          bounds = _html2canvas.Util.Bounds(el);
        }
        loadGradientImage(background_image.value, bounds);
      }
    });
  }

  function getImages (el) {
    var elNodeType = false;

    // Firefox fails with permission denied on pages with iframes
    try {
      Util.Children(el).forEach(getImages);
    }
    catch( e ) {}

    try {
      elNodeType = el.nodeType;
    } catch (ex) {
      elNodeType = false;
      Util.log("html2canvas: failed to access some element's nodeType - Exception: " + ex.message);
    }

    if (elNodeType === 1 || elNodeType === undefined) {
      loadPseudoElementImages(el);
      try {
        loadBackgroundImages(Util.getCSS(el, 'backgroundImage'), el);
      } catch(e) {
        Util.log("html2canvas: failed to get background-image - Exception: " + e.message);
      }
      loadBackgroundImages(el);
    }
  }

  function setImageLoadHandlers(img, imageObj) {
    img.onload = function() {
      if ( imageObj.timer !== undefined ) {
        // CORS succeeded
        window.clearTimeout( imageObj.timer );
      }

      images.numLoaded++;
      imageObj.succeeded = true;
      img.onerror = img.onload = null;
      start();
    };
    img.onerror = function() {
      if (img.crossOrigin === "anonymous") {
        // CORS failed
        window.clearTimeout( imageObj.timer );

        // let's try with proxy instead
        if ( options.proxy ) {
          var src = img.src;
          img = new Image();
          imageObj.img = img;
          img.src = src;

          proxyGetImage( img.src, img, imageObj );
          return;
        }
      }

      images.numLoaded++;
      images.numFailed++;
      imageObj.succeeded = false;
      img.onerror = img.onload = null;
      start();
    };
  }

  methods = {
    loadImage: function( src ) {
      var img, imageObj;
      if ( src && images[src] === undefined ) {
        img = new Image();
        if ( src.match(/data:image\/.*;base64,/i) ) {
          img.src = src.replace(/url\(['"]{0,}|['"]{0,}\)$/ig, '');
          imageObj = images[src] = {
            img: img
          };
          images.numTotal++;
          setImageLoadHandlers(img, imageObj);
        } else if ( isSameOrigin( src ) || options.allowTaint ===  true ) {
          imageObj = images[src] = {
            img: img
          };
          images.numTotal++;
          setImageLoadHandlers(img, imageObj);
          img.src = src;
        } else if ( supportCORS && !options.allowTaint && options.useCORS ) {
          // attempt to load with CORS

          img.crossOrigin = "anonymous";
          imageObj = images[src] = {
            img: img
          };
          images.numTotal++;
          setImageLoadHandlers(img, imageObj);
          img.src = src;
        } else if ( options.proxy ) {
          imageObj = images[src] = {
            img: img
          };
          images.numTotal++;
          proxyGetImage( src, img, imageObj );
        }
      }

    },
    cleanupDOM: function(cause) {
      var img, src;
      if (!images.cleanupDone) {
        if (cause && typeof cause === "string") {
          Util.log("html2canvas: Cleanup because: " + cause);
        } else {
          Util.log("html2canvas: Cleanup after timeout: " + options.timeout + " ms.");
        }

        for (src in images) {
          if (images.hasOwnProperty(src)) {
            img = images[src];
            if (typeof img === "object" && img.callbackname && img.succeeded === undefined) {
              // cancel proxy image request
              window[img.callbackname] = undefined; // to work with IE<9  // NOTE: that the undefined callback property-name still exists on the window object (for IE<9)
              try {
                delete window[img.callbackname];  // for all browser that support this
              } catch(ex) {}
              if (img.script && img.script.parentNode) {
                img.script.setAttribute("src", "about:blank");  // try to cancel running request
                img.script.parentNode.removeChild(img.script);
              }
              images.numLoaded++;
              images.numFailed++;
              Util.log("html2canvas: Cleaned up failed img: '" + src + "' Steps: " + images.numLoaded + " / " + images.numTotal);
            }
          }
        }

        // cancel any pending requests
        if(window.stop !== undefined) {
          window.stop();
        } else if(document.execCommand !== undefined) {
          document.execCommand("Stop", false);
        }
        if (document.close !== undefined) {
          document.close();
        }
        images.cleanupDone = true;
        if (!(cause && typeof cause === "string")) {
          start();
        }
      }
    },

    renderingDone: function() {
      if (timeoutTimer) {
        window.clearTimeout(timeoutTimer);
      }
    }
  };

  if (options.timeout > 0) {
    timeoutTimer = window.setTimeout(methods.cleanupDOM, options.timeout);
  }

  Util.log('html2canvas: Preload starts: finding background-images');
  images.firstRun = true;

  getImages(element);

  Util.log('html2canvas: Preload: Finding images');
  // load <img> images
  for (i = 0; i < imgLen; i+=1){
    methods.loadImage( domImages[i].getAttribute( "src" ) );
  }

  images.firstRun = false;
  Util.log('html2canvas: Preload: Done.');
  if (images.numTotal === images.numLoaded) {
    start();
  }

  return methods;
};

_html2canvas.Renderer = function(parseQueue, options){

  // http://www.w3.org/TR/CSS21/zindex.html
  function createRenderQueue(parseQueue) {
    var queue = [],
    rootContext;

    rootContext = (function buildStackingContext(rootNode) {
      var rootContext = {};
      function insert(context, node, specialParent) {
        var zi = (node.zIndex.zindex === 'auto') ? 0 : Number(node.zIndex.zindex),
        contextForChildren = context, // the stacking context for children
        isPositioned = node.zIndex.isPositioned,
        isFloated = node.zIndex.isFloated,
        stub = {node: node},
        childrenDest = specialParent; // where children without z-index should be pushed into

        if (node.zIndex.ownStacking) {
          // '!' comes before numbers in sorted array
          contextForChildren = stub.context = { '!': [{node:node, children: []}]};
          childrenDest = undefined;
        } else if (isPositioned || isFloated) {
          childrenDest = stub.children = [];
        }

        if (zi === 0 && specialParent) {
          specialParent.push(stub);
        } else {
          if (!context[zi]) { context[zi] = []; }
          context[zi].push(stub);
        }

        node.zIndex.children.forEach(function(childNode) {
          insert(contextForChildren, childNode, childrenDest);
        });
      }
      insert(rootContext, rootNode);
      return rootContext;
    })(parseQueue);

    function sortZ(context) {
      Object.keys(context).sort().forEach(function(zi) {
        var nonPositioned = [],
        floated = [],
        positioned = [],
        list = [];

        // positioned after static
        context[zi].forEach(function(v) {
          if (v.node.zIndex.isPositioned || v.node.zIndex.opacity < 1) {
            // http://www.w3.org/TR/css3-color/#transparency
            // non-positioned element with opactiy < 1 should be stacked as if it were a positioned element with ‘z-index: 0’ and ‘opacity: 1’.
            positioned.push(v);
          } else if (v.node.zIndex.isFloated) {
            floated.push(v);
          } else {
            nonPositioned.push(v);
          }
        });

        (function walk(arr) {
          arr.forEach(function(v) {
            list.push(v);
            if (v.children) { walk(v.children); }
          });
        })(nonPositioned.concat(floated, positioned));

        list.forEach(function(v) {
          if (v.context) {
            sortZ(v.context);
          } else {
            queue.push(v.node);
          }
        });
      });
    }

    sortZ(rootContext);

    return queue;
  }

  function getRenderer(rendererName) {
    var renderer;

    if (typeof options.renderer === "string" && _html2canvas.Renderer[rendererName] !== undefined) {
      renderer = _html2canvas.Renderer[rendererName](options);
    } else if (typeof rendererName === "function") {
      renderer = rendererName(options);
    } else {
      throw new Error("Unknown renderer");
    }

    if ( typeof renderer !== "function" ) {
      throw new Error("Invalid renderer defined");
    }
    return renderer;
  }

  return getRenderer(options.renderer)(parseQueue, options, document, createRenderQueue(parseQueue.stack), _html2canvas);
};

_html2canvas.Util.Support = function (options, doc) {

  function supportSVGRendering() {
    var img = new Image(),
    canvas = doc.createElement("canvas"),
    ctx = (canvas.getContext === undefined) ? false : canvas.getContext("2d");
    if (ctx === false) {
      return false;
    }
    canvas.width = canvas.height = 10;
    img.src = [
    "data:image/svg+xml,",
    "<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10'>",
    "<foreignObject width='10' height='10'>",
    "<div xmlns='http://www.w3.org/1999/xhtml' style='width:10;height:10;'>",
    "sup",
    "</div>",
    "</foreignObject>",
    "</svg>"
    ].join("");
    try {
      ctx.drawImage(img, 0, 0);
      canvas.toDataURL();
    } catch(e) {
      return false;
    }
    _html2canvas.Util.log('html2canvas: Parse: SVG powered rendering available');
    return true;
  }

  // Test whether we can use ranges to measure bounding boxes
  // Opera doesn't provide valid bounds.height/bottom even though it supports the method.

  function supportRangeBounds() {
    var r, testElement, rangeBounds, rangeHeight, support = false;

    if (doc.createRange) {
      r = doc.createRange();
      if (r.getBoundingClientRect) {
        testElement = doc.createElement('boundtest');
        testElement.style.height = "123px";
        testElement.style.display = "block";
        doc.body.appendChild(testElement);

        r.selectNode(testElement);
        rangeBounds = r.getBoundingClientRect();
        rangeHeight = rangeBounds.height;

        if (rangeHeight === 123) {
          support = true;
        }
        doc.body.removeChild(testElement);
      }
    }

    return support;
  }

  return {
    rangeBounds: supportRangeBounds(),
    svgRendering: options.svgRendering && supportSVGRendering()
  };
};
window.html2canvas = function(elements, opts) {
  elements = (elements.length) ? elements : [elements];
  var queue,
  canvas,
  options = {
    // general
    logging: false,
    elements: elements,
    background: "#fff",

    // preload options
    proxy: null,
    timeout: 0,    // no timeout
    useCORS: false, // try to load images as CORS (where available), before falling back to proxy
    allowTaint: false, // whether to allow images to taint the canvas, won't need proxy if set to true

    // parse options
    svgRendering: false, // use svg powered rendering where available (FF11+)
    ignoreElements: "IFRAME|OBJECT|PARAM",
    useOverflow: true,
    letterRendering: false,
    chinese: false,
    async: false, // If true, parsing will not block, but if the user scrolls during parse the image can get weird

    // render options
    width: null,
    height: null,
    taintTest: true, // do a taint test with all images before applying to canvas
    renderer: "Canvas"
  };

  options = _html2canvas.Util.Extend(opts, options);

  _html2canvas.logging = options.logging;
  options.complete = function( images ) {

    if (typeof options.onpreloaded === "function") {
      if ( options.onpreloaded( images ) === false ) {
        return;
      }
    }
    _html2canvas.Parse( images, options, function(queue) {
      if (typeof options.onparsed === "function") {
        if ( options.onparsed( queue ) === false ) {
          return;
        }
      }

      canvas = _html2canvas.Renderer( queue, options );

      if (typeof options.onrendered === "function") {
        options.onrendered( canvas );
      }
    });
  };

  // for pages without images, we still want this to be async, i.e. return methods before executing
  window.setTimeout( function(){
    _html2canvas.Preload( options );
  }, 0 );

  return {
    render: function( queue, opts ) {
      return _html2canvas.Renderer( queue, _html2canvas.Util.Extend(opts, options) );
    },
    parse: function( images, opts ) {
      return _html2canvas.Parse( images, _html2canvas.Util.Extend(opts, options) );
    },
    preload: function( opts ) {
      return _html2canvas.Preload( _html2canvas.Util.Extend(opts, options) );
    },
    log: _html2canvas.Util.log
  };
};

window.html2canvas.log = _html2canvas.Util.log; // for renderers
window.html2canvas.Renderer = {
  Canvas: undefined // We are assuming this will be used
};
_html2canvas.Renderer.Canvas = function(options) {
  options = options || {};

  var doc = document,
  safeImages = [],
  testCanvas = document.createElement("canvas"),
  testctx = testCanvas.getContext("2d"),
  Util = _html2canvas.Util,
  canvas = options.canvas || doc.createElement('canvas');

  function createShape(ctx, args) {
    ctx.beginPath();
    args.forEach(function(arg) {
      ctx[arg.name].apply(ctx, arg['arguments']);
    });
    ctx.closePath();
  }

  function safeImage(item) {
    if (safeImages.indexOf(item['arguments'][0].src ) === -1) {
      testctx.drawImage(item['arguments'][0], 0, 0);
      try {
        testctx.getImageData(0, 0, 1, 1);
      } catch(e) {
        testCanvas = doc.createElement("canvas");
        testctx = testCanvas.getContext("2d");
        return false;
      }
      safeImages.push(item['arguments'][0].src);
    }
    return true;
  }

  function renderItem(ctx, item) {
    switch(item.type){
      case "variable":
        ctx[item.name] = item['arguments'];
        break;
      case "function":
        switch(item.name) {
          case "createPattern":
            if (item['arguments'][0].width > 0 && item['arguments'][0].height > 0) {
              try {
                ctx.fillStyle = ctx.createPattern(item['arguments'][0], "repeat");
              }
              catch(e) {
                Util.log("html2canvas: Renderer: Error creating pattern", e.message);
              }
            }
            break;
          case "drawShape":
            createShape(ctx, item['arguments']);
            break;
          case "drawImage":
            if (item['arguments'][8] > 0 && item['arguments'][7] > 0) {
              if (!options.taintTest || (options.taintTest && safeImage(item))) {
                ctx.drawImage.apply( ctx, item['arguments'] );
              }
            }
            break;
          default:
            ctx[item.name].apply(ctx, item['arguments']);
        }
        break;
    }
  }

  return function(parsedData, options, document, queue, _html2canvas) {
    var ctx = canvas.getContext("2d"),
    newCanvas,
    bounds,
    fstyle,
    zStack = parsedData.stack;

    canvas.width = canvas.style.width =  options.width || zStack.ctx.width;
    canvas.height = canvas.style.height = options.height || zStack.ctx.height;

    fstyle = ctx.fillStyle;
    ctx.fillStyle = (Util.isTransparent(parsedData.backgroundColor) && options.background !== undefined) ? options.background : parsedData.backgroundColor;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = fstyle;

    queue.forEach(function(storageContext) {
      // set common settings for canvas
      ctx.textBaseline = "bottom";
      ctx.save();

      if (storageContext.transform.matrix) {
        ctx.translate(storageContext.transform.origin[0], storageContext.transform.origin[1]);
        ctx.transform.apply(ctx, storageContext.transform.matrix);
        ctx.translate(-storageContext.transform.origin[0], -storageContext.transform.origin[1]);
      }

      if (storageContext.clip){
        ctx.beginPath();
        ctx.rect(storageContext.clip.left, storageContext.clip.top, storageContext.clip.width, storageContext.clip.height);
        ctx.clip();
      }

      if (storageContext.ctx.storage) {
        storageContext.ctx.storage.forEach(function(item) {
          renderItem(ctx, item);
        });
      }

      ctx.restore();
    });

    Util.log("html2canvas: Renderer: Canvas renderer done - returning canvas obj");

    if (options.elements.length === 1) {
      if (typeof options.elements[0] === "object" && options.elements[0].nodeName !== "BODY") {
        // crop image to the bounds of selected (single) element
        bounds = _html2canvas.Util.Bounds(options.elements[0]);
        newCanvas = document.createElement('canvas');
        newCanvas.width = Math.ceil(bounds.width);
        newCanvas.height = Math.ceil(bounds.height);
        ctx = newCanvas.getContext("2d");

        ctx.drawImage(canvas, bounds.left, bounds.top, bounds.width, bounds.height, 0, 0, bounds.width, bounds.height);
        canvas = null;
        return newCanvas;
      }
    }

    return canvas;
  };
};
})(window,document);
(function () {
	angular
		.module('dmApp')
		.directive('dmBgColor', ['$compile', function($compile) {
			return {
				link: function(scope, element, attrs) {
					var bgColor = '<input minicolors type="text" ng-model="' + attrs.dmBgColor + '" />';
					element.prepend($compile(bgColor)(scope));
					element.wrap('<div class="dm-control-button dm-minicolor"></div>');
				}
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmOpacity', ['$compile', function($compile) {
			return {
				scope: {
					dmOpacity: '=',
					dmGradient: '='
				},
				link: link
			};

			function link(scope, element) {
				element.addClass('dm-slider-holder');
				scope.$watch('dmGradient', function (newValue) {
					if (newValue) {
						scope.gradient = {
							"background": "linear-gradient(to right, #fff 0%, " + newValue + " 100%)"
						};
					}
				});
				var opacity = '<div ui-slider min="0" max="1" step="0.01" use-decimals dm-convert-opacity ng-model="dmOpacity" ng-style="gradient"></div>';
				element.prepend($compile(opacity)(scope));
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('changeFont', ['fonts', '$rootScope', function (fontsService, $rootScope) {
			return {
				restrict: 'EA',
				controller: controller
			};

			function controller($scope) {
				// TODO some functionality needs to be in common.js
				$rootScope.$on('loaded-fonts', function () {
					$scope.googleFonts = fontsService.getGoogleFonts();
					$scope.fontlinks = '';
					for (var i = 0; i != $scope.googleFonts.length; ++i) {
						var family = $scope.googleFonts[i].key.replace(' ', '+');
						$scope.fontlinks += "<link href='https://fonts.googleapis.com/css?family=" + family + ":400,700' rel='stylesheet' type='text/css'>"
					}

					// TODO it's hack for 'Muli' italic. If you don't need this font - delete this
					$scope.fontlinks += "<link href='http://fonts.googleapis.com/css?family=Muli:400italic' rel='stylesheet' type='text/css'>";

					$('div[change-font]').html($scope.fontlinks);
				});
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmConvertOpacity', ['$compile', function ($compile) {
			return {
				require: 'ngModel',
				link: function(scope, element, attrs, ngModel) {
					function convert(item) {
						if (item != undefined) {
							return 1 - item;
						}
					}

					ngModel.$parsers.push(convert);
					ngModel.$formatters.push(convert);
				}
			}
		}])
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmEditUrl', ['$compile', 'URLValidator', 'toggleWidget', 'widgetIcons', function($compile, URLValidator, toggleWidget, widgetIcons) {
			return {
				scope: {
					dmEditUrl: '='
				},
				link: link
			};

			function link($scope, $element, $attrs) {
				var inputForm = '<div class="dm-edit-url ' + ($attrs.dmEditUrlType || '') + '" ng-class="{show: active}">' +
									'<a ng-click="open()" class="dm-edit-url-toggle">' +
										'<i class="dm-widget-icon">' +
											widgetIcons.getIcon($attrs.dmEditUrlType || 'url') +
										'</i>' +
									'</a>' +
									'<div class="dm-edit-url-input">' +
										'<input placeholder="' + (($attrs.dmMasonryTitle) ? 'Edit a Title' : 'Enter a Link') + '" class="dm-create-link" ng-keyup="keyupEvent($event)" type="text" />' +
									'</div>' +
								'</div>';

				$element.prepend($compile(inputForm)($scope));

				var inputEl = $element.find('.dm-create-link:first'),
					savedUrl = inputEl.val(),
					intervalId,
					customizerParent = $element.closest('.dm-popover'),
					arrow = customizerParent.find('.arrow'),
					template = $element.closest('.dm-template');

				$scope.$on('init-customizer', function() {
					$scope.active = false;
					$scope.saveLink = false;
					$scope.$apply();
				});

				angular.element('body').on('click', function (e) {
					if($(e.target).parents('.dm-popover').length === 0) {
						$scope.active = false;
						$scope.saveLink = false;
						$element.closest('.dm-popover').removeClass('focus in').hide();
						_clearInterval();
						toggleWidget.hide();
						$scope.$apply();
					}
				});

				inputEl.on('keydown', function() {
					$scope.saveLink = !!URLValidator.init(inputEl.val());
				});

				function _clearInterval() {
					if(intervalId) {
						clearInterval(intervalId);
					}
				}

				$scope.open = function() {
					var leftPosition = customizerParent.offset().left - template.offset().left;
					$scope.active = true;
					inputEl.closest('.dm-popover').addClass('focus');

					_clearInterval();

					intervalId = setInterval(function() {
						if(leftPosition + $element.outerWidth() > template.width()) {
							customizerParent.css({
								'left': 'auto',
								'right': 5 + 'px'
							});
							arrow.css({
								'left': leftPosition - (template.width() - $element.outerWidth() - 30) + 36
							})
						} else {
							customizerParent.css({
								'left': customizerParent.offset().left - template.offset().left + 'px',
								'right': 'auto'
							})
						}
					}, 10);
					setTimeout(function() {
						_clearInterval();
					}, 400);

					inputEl.val('');
					setTimeout(function() {
						inputEl.focus().val($scope.dmEditUrl);
					}, 0);
				};

				$scope.keyupEvent = function (e) {
					if (e.keyCode == 27) {
						$scope.active = false;
						$element.find('.dm-create-link').val(savedUrl);
						$element.closest('.dm-popover').removeClass('focus in').hide();
						_clearInterval();
					}
					if (e.keyCode == 13 && $scope.saveLink) {
						$scope.active = false;
						$scope.dmEditUrl = $element.find('.dm-create-link').val();
						$element.closest('.dm-popover').removeClass('focus in').hide();
						_clearInterval();
					}
				};
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmExternalNav', ['$compile', 'widgetIcons', function($compile, widgetIcons) {
			return {
				scope: {
					dmExternalNav: '@'
				},
				link: link
			};

			function link($scope, $element) {
				var linkExternalNav = '<a href="[[ dmExternalNav ]]" target="_blank" class="dm-goto-external-nav">' +
					'<span class="dm-goto-external-nav-text">Edit Menu</span>' +
					'<span class="dm-goto-external-nav-icon">' +
						'<i class="dm-widget-icon">' +
							widgetIcons.getIcon('new-window') +
						'</i>' +
					'</span>' +
					'</a>';

				$element.prepend($compile(linkExternalNav)($scope));
				$element.closest('.dm-popover').addClass('dm-external-nav');
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmContenteditable', [function() {
			return {
				restrict: "A",
				require: "ngModel",
				link: link
			};

			function link(scope, element, attrs, ngModel) {
				element.bind("click", function(e) {
					e.preventDefault();
				});

				element.attr("contenteditable", true);

				function read() {
					ngModel.$setViewValue(element.html());
				}

				ngModel.$render = function() {
					element.html(ngModel.$viewValue || "");
				};

				element.bind("keydown", function(e) {
					if (e.keyCode == 13) {
						document.execCommand('insertHTML', false, '<br><br>');
						return false;
					}
				});

				element.bind("blur keyup change", function(e) {
					scope.$apply(read);
				});
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmRemoveMedia', ['$compile', '$timeout', 'widgetIcons', function($compile, $timeout, widgetIcons) {
			return {
				scope: {
					dmRemoveMedia: '='
				},
				link: link,
				controller: controller
			};

			function link(scope, element) {
				var removeFile = '<div class="dm-remove-media">' +
					'<i class="dm-widget-icon" ng-click="removeFile()">' +
					'<span class="dm-remove-media-icon"></span>' +
					'</i>' +
					'</div>';
				element.prepend($compile(removeFile)(scope));
			}

			function controller($scope) {
				$scope.removeFile = function() {
					$timeout(function() {
						var isConfirmed = confirm('Are you sure you want to delete this image?');
					  if ( isConfirmed ) {
							var origUrl = $scope.dmRemoveMedia;

							$scope.dmRemoveMedia = 'img/1x1.png';

							apiCall(
								'attachement.delete',
								{
									attachement_url : origUrl
								},
								function() {
									updateControls(hasDifferences());
								},
								function(code, msg) {
									if ( code != 247614 ) {
										$scope.dmRemoveMedia = origUrl;
										updateControls(hasDifferences());
										alert(msg);
									}
								}
							);
						}
					});
				}
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('dmSlider', ['$compile', 'widgetIcons', function($compile, widgetIcons) {
			return {
				scope: {
					dmSlider: '@',
					dmSliderModel: '=',
					dmSliderArchive: '@'
				},
				link: link,
				controller: controller
			};

			function link(scope, element) {
				element.attr('id', scope.dmSlider);
				element.addClass('dm-carousel-holder');
				var parentScope = element.closest('.dm-template').scope(),
					flagName = scope.dmSliderArchive + 'Flag',
					slideName = scope.dmSliderArchive + 'Slide';

				parentScope[flagName] = [];
				parentScope[slideName] = {};

				scope.$watchCollection('$parent["' + flagName + '"]', function(newValue) {
					var counter = [];
					if (newValue) {
						for(var i = 0; i < newValue.length; i++) {
							if(newValue[i]) {
								parentScope[slideName] = scope.dmSliderModel[i];
								counter.push('<span class="active"></span>');
							} else {
								counter.push('<span></span>');
							}
						}
						element.find('.dm-carousel-counter').empty().append(counter.join('\n\n'));
					}
				});

				var slideControls = '<div class="dm-controls dm-carousel">' +
					'<div class="btn-group">' +
					'<a class="dm-control-button prev">' +
					'<i class="dm-widget-icon">' +
					widgetIcons.getIcon('prev') +
					'</i>' +
					'</a>' +
					'<div class="dm-control-button dm-carousel-hide">' +
					'<a ng-click="addSlide()" class="dm-carousel-add" href="#">' +
					'<i class="dm-widget-icon">' +
					widgetIcons.getIcon('add') +
					'</i> Add slide</a>' +
					'<div class="dm-carousel-counter"></div>' +
					'<a ng-click="removeSlide()" class="dm-carousel-remove" href="#">' +
					'<i class="dm-widget-icon">' +
					widgetIcons.getIcon('remove') +
					'</i> Remove slide</a>' +
					'</div>' +
					'<a class="dm-control-button next">' +
					'<i class="dm-widget-icon">' +
					widgetIcons.getIcon('next') +
					'</i>' +
					'</a>' +
					'</div>' +
					'</div>';

				element.append($compile(slideControls)(scope));

				scope.$watch('dmSliderModel', function (newValue) {
					if (newValue && newValue.length <= 1) {
						element.find('.dm-carousel').addClass('showAlways');
						element.closest('.dm-template').find('.pt-control-prev, .pt-control-next, .controls, .pt-indicators, .carousel-indicators, .content-35-customPager').css({visibility: 'hidden'});
					}
					else if (newValue && newValue.length > 1) {
						element.find('.dm-carousel').removeClass('showAlways');
						element.closest('.dm-template').find('.pt-control-prev, .pt-control-next, .controls, .pt-indicators, .carousel-indicators, .content-35-customPager').css({visibility: 'visible'});
					}
				});

				scope.addSlide = function() {
					var newItem = angular.copy(parentScope[slideName]);
					newItem['generated'] = true;
					scope.dmSliderModel.push(newItem);
					if (scope.dmSliderModel.length > 1) {
						element.find('.dm-carousel').removeClass('showAlways');
						element.closest('.dm-template').find('.pt-control-prev, .pt-control-next, .controls, .pt-indicators, .carousel-indicators, .content-35-customPager').css({visibility: 'visible'});
					}
				};

				scope.removeSlide = function () {
					var sliderFlagArray = parentScope[flagName];
					if(sliderFlagArray.length > 1) {
						var isConfirmed = confirm('Are you sure you want to remove this slide?');
						if ( isConfirmed ) {
							for(var i = 0; i < sliderFlagArray.length; i++) {
								if(sliderFlagArray[i]) {
									scope.dmSliderModel.splice(i, 1);
									sliderFlagArray.splice(i, 1);
									setTimeout(function() {
										scope.$parent.extInit.slider(scope.dmSlider);
									}, 0);
									if (scope.dmSliderModel.length <= 1) {
										element.find('.dm-carousel').addClass('showAlways');
										element.closest('.dm-template').find('.pt-control-prev, .pt-control-next, .controls, .pt-indicators, .carousel-indicators, .content-35-customPager').css({visibility: 'hidden'});
									}
								}
							}
							sliderFlagArray[0] = true;
						}
					}
				};
			}

			function controller(scope) {
				this.getId = function() {
					return scope.dmSlider;
				};
				this.getArchive = function() {
					return scope.dmSliderArchive;
				}
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('contenteditable', function() {
			return {
				restrict: 'A',
				require: 'ngModel',
				link: function(scope, element, attrs, ngModel) {
					ngModel.$render = function() {
						element.html(ngModel.$viewValue || '');
					};

					element.bind('blur keyup change', function() {
						scope.$apply(function () {
							var tag = $('<p>' + element.html() + '</p>');
							tag.find('div').replaceWith(function () { return $(this).contents(); });
							ngModel.$setViewValue(tag.html());
						});
					});

					ngModel.$setViewValue(element.html());
				}
			};
		});
})();

(function () {
	angular
		.module('dmApp')
		.directive('ngEnterLink', function () {
			return {
				restrict: 'A',
				link: function (scope, element, attrs) {
					element.bind('keydown keypress', function (event) {
						if (event.which === 13) {
							// for preventing inserting <br> in Chrome
							event.preventDefault();

							scope.$apply(function () {
								scope.addLink();
							});
						}
					});

					element.bind('keyup', function (event) {
						scope.$apply(function () {
							scope.validLink = !!$(event.target).val();
						});
					});
				}
			};
		});
})();

(function () {
	angular
		.module('dmApp')
		.directive('textEditing', ['fonts', 'compatibility', '$timeout', '$rootScope', function (fontsService, compatibilityService, $timeout, $rootScope) {
			return {
				scope: {
					ngModel: '=ngModel'
				},
				require: 'ngModel',
				restrict: 'A',
				templateUrl: pluginUrl + 'templates/ui-widgets/components/text-editing.directive/text-editing.html',
				link: link,
				controller: controller
			};

			function link (scope, element, attrs, ngModel) {
				scope.webFonts = fontsService.getWebFonts();
				scope.googleFonts = fontsService.getGoogleFonts();
				scope.$popover = $(element).find('[text-popover]');

				// hide link on grid 4-6
				if (attrs.textEditingExclude && attrs.textEditingExclude.indexOf('link') != -1) {
					scope.excludeLink = true;
				}

				// hide popover
				scope.editMode = false;

				// set default visible font for popover
				scope.visibleFont = 'Times New Roman';

				// render model for setting needed values into scope
				ngModel.$render = function () {
					if (ngModel.$modelValue) {
						scope.markup = ngModel.$modelValue.content;
						scope.style = ngModel.$modelValue.style;
						scope.classes = ngModel.$modelValue.classes;
					}
				};

				element.on('mousedown', function (e) {
					if (scope.changingSelection) {
						e.preventDefault();
					} else {
						scope.$apply(function () {
							scope.mouseDown = true;
							$rootScope.$emit('text-editing-hide');
						});
					}
				});

				element.on('keydown', function (e) {
					if (e.which === 65 && (e.ctrlKey || e.metaKey)) {
						$timeout(function () {
							scope.editMode = true;
						}, 100);
					} else if (e.which === 13) {
						scope.$apply(function () {
							e.preventDefault();

							var insertTag = '<br>';

							var el = $(element).find('[contenteditable]');
							var position = getCaretCharacterOffsetWithin(el[0]);
							var len = $(el).text().length;
							if (position === len) {
								insertTag += '<br>'
							}

							compatibilityService.isIE ? pasteHtmlAtCaret(insertTag) : document.execCommand('insertHTML', false, insertTag);
						});
					}
				});

				scope.$watch('markup', function (newValue) {
					if (newValue !== undefined && ngModel.$modelValue) {
						ngModel.$modelValue.content = newValue;
					}
				});

				// add watchers there at the time to adding new styles
				scope.$watch('style["color"]', function (newValue) {
					if (newValue) {
						ngModel.$modelValue.style['color'] = newValue;

						var colors = [];
						for (var i = 1; i < 7; i += 2) { colors.push(parseInt('0x' + newValue.substr(i, 2), 16)); }

						var colorPic = scope.$popover.find('.minicolors-swatch-color');
						var stroke = colors[0] < 128 && colors[1] < 128 && colors[2] < 128;

						if (stroke) {
							colorPic.css({'border': '2px solid #fff', 'box-shadow': 'inset 0 1px 2px 0 rgba(0,0,0,.25)'});
						} else {
							colorPic.css({'border': 'none', 'box-shadow': 'none'});
						}
					}
				});

				scope.$watch('style["font-family"]', function (newValue) {
					if (newValue) {
						ngModel.$modelValue.style['font-family'] = newValue;

						if (scope.webFonts.length && scope.googleFonts.length) {
							var wv = $.grep(scope.webFonts, function (e) {
								return e.value == newValue;
							});
							var gv = $.grep(scope.googleFonts, function (e) {
								return e.value == newValue;
							});

							if (wv.length != 0 || gv.length != 0) {
								scope.visibleFont = wv.length != 0 ? wv[0].key : gv[0].key;
							}

							scope.restoreSelectionAfterStyling();
						}
					}
				});

				// add watchers there at the time to adding new classes
				scope.$watch('classes["font-size"]', function (newValue) {
					if (newValue) {
						ngModel.$modelValue.classes['font-size'] = newValue;
					}
				});

				scope.$watch('classes["line-height"]', function (newValue) {
					if (newValue) {
						ngModel.$modelValue.classes['line-height'] = newValue;
					}
				});

				scope.saveLastSelection = saveLastSelection;
				scope.restoreLastSelection = restoreLastSelection;
				scope.removeSelection = removeSelection;
				scope.selectWholeTagContent = selectWholeTagContent;
				scope.loadFontsList = loadFontsList;

				scope.customizePopoverPosition = function (popover) {
					// TODO if will occur some changes in widget - recalculate this widget width
					var widgetWidth = 218,
						widgetHeight = 48,
						selection = window.getSelection(),
						range = selection.getRangeAt(0).cloneRange(),
						rectangle = range.getBoundingClientRect();

					if (scope.excludeLink) {
						widgetWidth -= 34;
					}

					// for solving chrome selection bug (top, left)
					var correctedSelectionTop = rectangle.top,
						correctedSelectionLeft = rectangle.left,
						correctedSelectionWidth = rectangle.width,
						subtracted = 1; // for checking whitespace at right position (usual double clicking)

					if (compatibilityService.isChrome) {
						scope.changingSelection = true;

						try {
							// modify contains only in webkit engine
							var text = range.toString();

							if (text.length > 1) {
								if (selection.anchorOffset > selection.focusOffset) {
									selection.modify('extend', 'right', 'character');
								} else {
									if (text !== ' ') {
										if (text[text.length - 1] === ' ') {
											selection.modify('extend', 'left', 'character');
											subtracted = 2;
										}

										selection.collapseToEnd();
										for (var i = 0; i < text.length - subtracted; ++i) {
											selection.modify('extend', 'left', 'character');
										}
									}
								}
							}

							var extendedRectangle = selection.getRangeAt(0).getBoundingClientRect();
							if (extendedRectangle.height !== rectangle.height) {
								correctedSelectionTop = extendedRectangle.top;

								var symbolsAmount = Math.abs(selection.focusOffset - selection.anchorOffset);
								var symbolWidth = extendedRectangle.width / symbolsAmount;
								correctedSelectionLeft = extendedRectangle.left - symbolWidth;
								correctedSelectionWidth = extendedRectangle.width + symbolWidth;
							}

							if (text !== ' ' && text.length > 1) {
								selection.modify('extend', 'left', 'character');
							}
						} catch (e) {	}

						scope.removeSelection();
						scope.restoreLastSelection(range);
						scope.changingSelection = false;
					}

					var left = correctedSelectionLeft - widgetWidth / 2 + correctedSelectionWidth / 2 - $(window).scrollLeft();
					var top = correctedSelectionTop - $(element).closest('.dm-template').offset().top - 8 - widgetHeight + $(window).scrollTop(); // 8 - arrow height

					var excludingRight = $(popover).width() + left - $(document).width() + 10;
					if (excludingRight > 0) {
						$(popover).find('.arrow').css({'left': widgetWidth / 2 + excludingRight});
						left -= excludingRight;
					} else {
						$(popover).find('.arrow').css({'left': widgetWidth / 2});
					}

					if (left < 0) {
						var truncated = 0 - left + 10;
						$(popover).find('.arrow').css({'left': widgetWidth / 2 - truncated});
						left += truncated;
					} else {
						$(popover).find('.arrow').css({'left': widgetWidth / 2});
					}

					$(popover).css({'top': top + 'px', 'left': left + 'px'});
				};

				scope.restoreSelectionAfterStyling = function () {
					if (scope.savedSelection) {
						scope.removeSelection();
						scope.restoreLastSelection(scope.savedSelection);
					}
				};

				// for showing popover at mouseup event
				scope.showAtMouseUp = function () {
					$timeout(function () {
						var selection = window.getSelection().toString();
						if (selection && scope.mouseDown) {
							scope.editMode = true;
						}

						scope.mouseDown = false;
					}, 100);
				};

				// update html for FF
				$rootScope.$on('text-editing-update', function () {
					var html = $(element).find('[contenteditable]').html();
					scope.markup = html;
				});

				$(element).find('[contenteditable]').on('paste drop', function (e) {
					e.preventDefault();

					var data = e.type === 'drop' ? e.originalEvent.dataTransfer : e.originalEvent.clipboardData;
					var text;
					var ie;
					if (!data) {
						data = window.clipboardData;
						text = data.getData('text');
						ie = true; // IE fix
					} else {
						var html = data.getData('text/html');
						text = html ? $('<p>' + html + '</p>').text() : data.getData('text/plain');
					}

					if (e.type === 'drop') {
						var event = e.originalEvent;
						createSelectionFromPoint(event.x, event.y, event.x, event.y);
					}

					text = text.trimStart().trimEnd();
					if (!ie) {
						document.execCommand('insertHTML', false, text);
					} else {
						pasteHtmlAtCaret(text);
					}
				});
			}

			function controller ($scope) {
				// it needed for initial font-family
				$scope.$watch('webFonts', function (val) {
					if (val.length && $scope.style) {
						var wv = $.grep($scope.webFonts, function (e) {
							return e.value == $scope.style['font-family'];
						});
						var gv = $.grep($scope.googleFonts, function (e) {
							return e.value == $scope.style['font-family'];
						});

						if (wv.length != 0 || gv.length != 0) {
							$scope.visibleFont = wv.length != 0 ? wv[0].key : gv[0].key;
						}
					}

					if (val.length) {
						$scope.loadFontsList($scope);
					}
				}, true);

				$rootScope.$on('text-editing-mouse-up', function () {
					$scope.showAtMouseUp();
				});

				$rootScope.$on('loaded-fonts', function () {
					loadFontsList($scope);
				});
			}

			function saveLastSelection () {
				return window.getSelection().getRangeAt(0);
			}

			function restoreLastSelection (savedSelection) {
				window.getSelection().addRange(savedSelection);
			}

			function removeSelection () {
				window.getSelection().removeAllRanges();
			}

			// for selecting <a href>
			function selectWholeTagContent (element) {
				var range, selection;

				selection = window.getSelection();
				range = document.createRange();
				range.selectNodeContents(element);
				selection.removeAllRanges();
				selection.addRange(range);
			}

			function loadFontsList (scope) {
				if (!scope.fonts) {
					scope.fonts = scope.webFonts.concat(scope.googleFonts);
					scope.fonts.sort(function (a, b) {
						if (a.key < b.key) return -1;
						if (a.key > b.key) return 1;
						return 0;
					});
				}
			}

			// hack for making selection below cursor
			function createSelectionFromPoint(startX, startY, endX, endY) {
				var doc = document;
				var start, end, range = null;
				if (typeof doc.caretPositionFromPoint != "undefined") {
					start = doc.caretPositionFromPoint(startX, startY);
					end = doc.caretPositionFromPoint(endX, endY);
					range = doc.createRange();
					range.setStart(start.offsetNode, start.offset);
					range.setEnd(end.offsetNode, end.offset);
				} else if (typeof doc.caretRangeFromPoint != "undefined") {
					start = doc.caretRangeFromPoint(startX, startY);
					end = doc.caretRangeFromPoint(endX, endY);
					range = doc.createRange();
					range.setStart(start.startContainer, start.startOffset);
					range.setEnd(end.startContainer, end.startOffset);
				}
				if (range !== null && typeof window.getSelection != "undefined") {
					var sel = window.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
				} else if (typeof doc.body.createTextRange != "undefined") {
					range = doc.body.createTextRange();
					range.moveToPoint(startX, startY);
					var endRange = range.duplicate();
					endRange.moveToPoint(endX, endY);
					range.setEndPoint("EndToEnd", endRange);
					range.select();
				}
			}

			// hack only for IE
			function pasteHtmlAtCaret (html) {
				var sel, range;
				// IE9 and non-IE
				sel = window.getSelection();
				if (sel.getRangeAt && sel.rangeCount) {
					range = sel.getRangeAt(0);
					range.deleteContents();

					// Range.createContextualFragment() would be useful here but is
					// only relatively recently standardized and is not supported in
					// some browsers (IE9, for one)
					var el = document.createElement("div");
					el.innerHTML = html;
					var frag = document.createDocumentFragment(), node, lastNode;
					while ((node = el.firstChild)) {
						lastNode = frag.appendChild(node);
					}
					range.insertNode(frag);

					// Preserve the selection
					if (lastNode) {
						range = range.cloneRange();
						range.setStartAfter(lastNode);
						range.collapse(true);
						sel.removeAllRanges();
						sel.addRange(range);
					}
				}
			}

			function getCaretCharacterOffsetWithin (element) {
				var caretOffset = 0;
				var doc = element.ownerDocument || element.document;
				var win = doc.defaultView || doc.parentWindow;
				var sel;
				if (typeof win.getSelection != "undefined") {
					sel = win.getSelection();
					if (sel.rangeCount > 0) {
						var range = win.getSelection().getRangeAt(0);
						var preCaretRange = range.cloneRange();
						preCaretRange.selectNodeContents(element);
						preCaretRange.setEnd(range.endContainer, range.endOffset);
						caretOffset = preCaretRange.toString().length;
					}
				} else if ((sel = doc.selection) && sel.type != "Control") {
					var textRange = sel.createRange();
					var preCaretTextRange = doc.body.createTextRange();
					preCaretTextRange.moveToElementText(element);
					preCaretTextRange.setEndPoint("EndToEnd", textRange);
					caretOffset = preCaretTextRange.text.length;
				}

				return caretOffset;
			}
		}]);
})();

(function () {
	angular
		.module('dmApp')
		.directive('textPopover', ['compatibility', '$timeout', '$rootScope', function (compatibilityService, $timeout, $rootScope) {
			return {
				restrict: 'A',
				templateUrl: pluginUrl + 'templates/ui-widgets/components/text-editing.directive/popover.html',
				link: link,
				controller: controller
			};

			function link (scope, element, attrs) {
				scope.restoreDefaults = restoreDefaults;

				scope.$watch('editMode', function (val) {
					scope.restoreDefaults(scope);

					var template = $(element).closest('.dm-template');
					if (val) {
						scope.customizePopoverPosition($(element).parent());
						$rootScope.$emit('text-editing-shown', scope.$id);

						template.addClass('active-template');
						$('body').on('wheel', '.dm-text-editing-font-list', onWheel);
					} else {
						template.removeClass('active-template');
						$('body').off('wheel', '.dm-text-editing-font-list', onWheel);
					}
				});

				scope.execBold = function () {
					document.execCommand('bold', false, null);
					scope.bold = !scope.bold;
				};

				scope.execItalic = function () {
					document.execCommand('italic', false, null);
					scope.italic = !scope.italic;
				};

				scope.triggerLinkEditor = function () {
					scope.savedSelection = scope.saveLastSelection();

					if (scope.existsLink && scope.changeLink) {
						scope.removeSelection();
						scope.restoreLastSelection(scope.savedSelection);
						var selection = window.getSelection();

						var el;
						if (!compatibilityService.isFirefox) {
							el = $(selection.focusNode).parent().closest('a')[0];
							if (!el) {
								el = selection.anchorNode;
							}
						} else {
							scope.selectWholeTagContent(getAnchor(selection));
						}

						document.execCommand('unlink', false, null);
						scope.existsLink = false;
						scope.link = undefined;
					}

					// ng-show on changeLink variable
					scope.changeLink = !scope.changeLink;

					if (!scope.changeLink) {
						scope.restoreSelectionAfterStyling();
					}
				};

				scope.triggerFontsEditor = function () {
					scope.changeFont = !scope.changeFont;

					var fontsListWithWidgetHeight = 344;
					if ($(element).parent().offset().top + fontsListWithWidgetHeight > $(document).height()) {
						// show fonts on the top position
						$(element).find('.dm-text-editing-font-dropDown').css({'top': '-296px'});
						$(element).find('.dm-text-editing-font-list').after($(element).find('.dm-text-editing-font-holder'));
					} else {
						$(element).find('.dm-text-editing-font-dropDown').css({'top': '62px'});
						$(element).find('.dm-text-editing-font-holder').after($(element).find('.dm-text-editing-font-list'));
					}

					if (scope.changeFont) {
						scope.savedSelection = scope.saveLastSelection();
						$timeout(function () {
							var fontsList = $(element).find('.dm-text-editing-font-list');
							var fontTag = fontsList.find('li').filter(function () { return $(this).text().indexOf(scope.visibleFont) !== -1 });
							fontsList.scrollTop(fontTag.position().top + 15);
						}, 100);
					}
				};

				scope.selectFont = function (event) {
					if (scope.style && event.target.textContent) {
						var key = event.target.textContent.replace(/^\s+/,"").replace(/\s+$/,"");

						var wk = $.grep(scope.webFonts, function(e) { return e.key == key; });
						var gk = $.grep(scope.googleFonts, function(e) { return e.key == key; });

						scope.style['font-family'] = wk.length != 0 ? wk[0].value : gk[0].value;
					}
				};

				scope.addLink = function () {
					if (scope.validLink) {
						// correct restore selection
						scope.removeSelection();
						scope.restoreLastSelection(scope.savedSelection);

						if (!scope.existsLink) {
							// make link
							document.execCommand('createLink', false, scope.link);
						} else {
							// just update exists link href
							var el;
							if (compatibilityService.isFirefox) {
								el = getAnchor(window.getSelection());
							} else if (compatibilityService.isIE) {
								el = window.getSelection().focusNode;
							} else {
								el = window.getSelection().focusNode.parentElement;
							}

							$(el).attr('href', scope.link);
						}

						// ng-show on changeLink variable
						scope.changeLink = false;

						// for showing yet added icon
						scope.existsLink = true;
					}
				};

				scope.applyClasses = function (classes) {
					if (classes !== undefined) {
						$(element).find('[ng-model*="font-size"]').children().text(classes['font-size']);
						$(element).find('[ng-model*="line-height"]').children().text(classes['line-height']);
						return 'font-size-' + classes['font-size'] + ' line-height-' + classes['line-height'];
					} else {
						return '';
					}
				};

				var el = $(element).parent();
				var template = $(el).closest('.dm-template');
				template.append($(el));

				scope.toggleFontsList = function () {
					if (scope.changeFont) {
						scope.changeFont = false;
					}
				};

				scope.reduceFontSize = function () {
					if (scope.classes && scope.classes['font-size'] > 1) {
						--scope.classes['font-size'];
					}
				};

				scope.increaseFontSize = function () {
					if (scope.classes && scope.classes['font-size'] < 7) {
						++scope.classes['font-size'];
					}
				};

				scope.reduceLineHeight = function () {
					if (scope.classes && scope.classes['line-height'] > 1) {
						--scope.classes['line-height'];
					}
				};

				scope.increaseLineHeight = function () {
					if (scope.classes && scope.classes['line-height'] < 7) {
						++scope.classes['line-height'];
					}
				};

				$(element).find('.dm-text-editing-font-holder').find('input').on('click', function () {
					this.select();
				});
			}

			function controller ($scope) {
				$rootScope.$on('text-editing-shown', function (event, id) {
					if ($scope.$id !== id) {
						$rootScope.textEditingId = id;
						$scope.editMode = false;
					}
				});

				$rootScope.$on('text-editing-hide', function () {
					if ($scope.editMode) {
						$scope.editMode = false;
					}
				});
			}

			function restoreDefaults (scope) {
				function foundLink (tag) {
					scope.existsLink = true;
					scope.validLink = true;
					scope.link = $(tag).attr('href');
				}

				// set default values
				scope.changeLink = false;
				scope.existsLink = false;
				scope.validLink = false;
				scope.link = undefined;

				scope.changeFont = false;

				// show yet selected settings of text block
				scope.bold = document.queryCommandState('bold');
				scope.italic = document.queryCommandState('italic');

				// trying get selection for getting possible parent link node
				var selection = window.getSelection();
				var tag;
				var found;

				var anchorNode = selection.anchorNode;
				var focusNode = selection.focusNode;
				if (anchorNode) {
					// for all good browsers
					tag = $(anchorNode).parent().closest('a');
					if (tag.prop('tagName') !== undefined && tag.prop('tagName').toLowerCase() === 'a') {
						found = true;
						// fix for FF
						if (compatibilityService.isFirefox) {
							if (selection.anchorNode != selection.focusNode) {
								found = false;
							}
						} else if (compatibilityService.isChrome) {
							if (anchorNode != focusNode && selection.focusOffset !== 1) {
								found = false;
							}
						}
						// fix for selecting text between two links
					} else if (anchorNode != focusNode && !compatibilityService.isFirefox) {
						// hack for IE or Safari
						var len1 = $(anchorNode).text().length;
						var len2 = $(focusNode).text().length;
						if (len1 === selection.anchorOffset && selection.focusOffset === 0) {
							// direct selection
							tag = $(anchorNode).nextUntil($(focusNode));
							found = true;
						} else if (selection.anchorOffset === 0 && selection.focusOffset === len2) {
							// revert selection
							tag = $(anchorNode).prevUntil($(focusNode));
							found = true;
						} else if ($(focusNode).closest('a').prop('tagName') !== undefined &&
							$(focusNode).closest('a').prop('tagName').toLowerCase() === 'a') {
							tag = $(focusNode).closest('a');
							found = true;
						}
					}

					if (found && tag.length == 1) {
						scope.selectWholeTagContent(tag[0]);
						foundLink(tag);
					} else {
						if (compatibilityService.isFirefox) {
							var el = getAnchor(selection);
							if (el) {
								foundLink(el);
							}
						}
					}
				}
			}

			function onWheel (e) {
				var curScrollPos = $(this).scrollTop();
				var wheelEvent = e.originalEvent;
				var dY = wheelEvent.deltaY;

				if ((dY > 0 && curScrollPos >= $(this)[0].scrollHeight - $(this).outerHeight()) ||
					(dY < 0 && curScrollPos <= 0)) {
					return false;
				}
			}

			// only for FF and IE
			function getAnchor (selection) {
				var children = selection.anchorNode.childNodes;
				var range = selection.getRangeAt(0);
				for (var i = 0; i < children.length; ++i) {
					if (children[i].nodeType !== 3) {
						if ($(children[i]).text() === range.toString() &&
							$(children[i]).prop('tagName') !== undefined &&
							$(children[i]).prop('tagName').toLowerCase() === 'a' &&
							i === selection.anchorOffset) {
							return children[i];
						}
					}
				}
			}
		}]);
})();
