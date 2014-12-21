/* ==========================================================
 * admin.js v1.0.0
 * http://thomasgriffinmedia.com
 * ==========================================================
 * Copyright 2013 Thomas Griffin.
 *
 * Licensed under the GPL License, Version 2.0 or later (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
;(function($){
	$(function(){
	    // Show the loading icon when connecting to the API.
	    $('#om-api-submit').on('click', function(){
    	    $('.om-loading').css('visibility', 'visible');
	    });

	    // Make users confirm the option to remove API data.
	    $('input[name="om-api-remove"]').on('click', function(e){
	        var r = confirm(optin_monster.confirm);
	        if ( ! r ) e.preventDefault();
	    });
	});
}(jQuery));