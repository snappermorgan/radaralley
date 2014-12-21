**2.1.4**

* **Fix**
	* Handled case where ICL_PLUGIN_PATH constant is not defined (i.e. when plugin is activated before WPML core)
	* Fixed Korean locale in .mo file name

**2.1.3**

* **Fix**
	* Handled dependency from SitePress::get_setting()
	* Updated translations
	* Several fixes to achieve compatibility with WordPress 3.9
	* Updated links to wpml.org

**2.1.2**

* **Performances**
	* Reduced the number of calls to *$sitepress->get_current_language()*, *$this->get_active_languages()* and *$this->get_default_language()*, to avoid running the same queries more times than needed
* **Feature**
	* Added WPML capabilities (see online documentation)
* **Fix**
	* Improved SSL support for included CSS and JavaScript files
