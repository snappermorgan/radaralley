<?php

/**
 * Represents all of the units and modules in a single course.
 */
class WPCW_CourseMap 
{	
	/**
	 * The complete course details (if the course is valid).
	 * @var Object
	 */
	protected $courseDetails;
	
	/**
	 * A list of the modules, in their absolute order.
	 * @var Array
	 */
	protected $moduleList;
	
	
	/**
	 * A list of the units, in their absolute order.
	 * @var Array
	 */
	protected $unitList;
		
	/**
	 * A structured list of units and modules, in the order that they should be rendered.
	 * @var Array
	 */
	protected $completeList;
	
	
	/**
	 * Create course map object.
	 */
	public function __construct() 
	{
		$this->courseDetails = false;
		
		$this->unitList = false;
		$this->completeList = false;
		$this->moduleList = false;
	}
	
	
	/**
	 * Return the number of units in the course.
	 */
	public function getUnitCount()
	{
		if (empty($this->unitList)) {
			return false;
		}
		
		return count($this->unitList);
	}
	
	/**
	 * Return the course details.
	 * @return Object The course details.
	 */
	public function getCourseDetails() {
		return $this->courseDetails;
	}

	
	/**
	 * Try to load details using a course ID.
	 * @param Integer $courseID The ID of the course to use to load details for this course.
	 */
	public function loadDetails_byCourseID($courseID)
	{
		$this->courseDetails = WPCW_courses_getCourseDetails($courseID);
		$this->loadUnitsForCourse();
	}
	
	
	/**
	 * Try to load details using a module ID.
	 * @param Integer $moduleID The ID of the module to use to load details for this course.
	 */	
	public function loadDetails_byModuleID($moduleID)
	{
		$moduleDetails = WPCW_modules_getModuleDetails($moduleID);
		if (!$moduleDetails) {
			return;
		}
		
		$this->courseDetails = WPCW_courses_getCourseDetails($moduleDetails->parent_course_id);
		$this->loadUnitsForCourse();
	}
	
	
	/**
	 * Try to load details using a single unit.
	 * @param Integer $unitID The ID of the unit to use to load details for this course.
	 */
	public function loadDetails_byUnitID($unitID)
	{
		global $wpcwdb, $wpdb;
		$wpdb->show_errors();
		
		// Get a list of all units for this course in absolute order
		$parentCourseID = $wpdb->get_var($wpdb->prepare("
			SELECT parent_course_id
			FROM $wpcwdb->units_meta
			WHERE unit_id = %d			
		", $unitID));
		
		$this->courseDetails = WPCW_courses_getCourseDetails($parentCourseID);
		$this->loadUnitsForCourse();		

	}
	
	
	/**
	 * Prepare the internal lists of details based on what course we've got
	 * to create an ordered list of modules and units.
	 */
	public function loadUnitsForCourse()
	{
		// Can't do anything. There are no course details.
		if (!$this->courseDetails) {	
			return false;
		}
		
		global $wpcwdb, $wpdb;
		$wpdb->show_errors();
				
		$this->moduleList = array();	
		$this->unitList = array();
		$this->completeList = array();
	
		// Get a list of all modules, in order.	
		$moduleList = $wpdb->get_results($wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->modules			
			WHERE parent_course_id = %d
			ORDER BY module_order, module_title ASC
			", $this->courseDetails->course_id));
		
		if (!empty($moduleList))
		{
			$previousModule = false;
			foreach ($moduleList as $moduleDetails)
			{
				// Module added with ID => Details
				$this->moduleList[$moduleDetails->module_id] = $moduleDetails;
				

				
				// Now get the units for this module.
				$unitList = $wpdb->get_results($wpdb->prepare("
					SELECT *
					FROM $wpcwdb->units_meta
					WHERE parent_course_id = %d
					  AND parent_module_id = %d
					ORDER BY unit_order ASC			
				", $this->courseDetails->course_id, $moduleDetails->module_id));
				
				$unitSublist = array();
				if (!empty($unitList))
				{
					foreach ($unitList as $unitObj)
					{
						// Unit added with ID => Details
						$this->unitList[$unitObj->unit_id] = $unitObj;
						
						// Unit added with ID => Details
						$unitSublist[$unitObj->unit_id] = $unitObj;
					}
				}
				
				// Creating a list of modules and their units in the detailed list
				$this->completeList[$moduleDetails->module_id] = array(
					'units' 	=> $unitSublist,
					'details' 	=> $moduleDetails,
					'previous'	=> $previousModule
				);
				
				// Store previous module for nav.
				$previousModule = $moduleDetails->module_id;
			} // end of module loop
		} // end of module list check
	} // end of public function
	
	
	/**
	 * Get all unit IDs for the whole course.
	 * @return Array The list IDs for units for the whole course.
	 */
	public function getUnitIDList_forCourse()
	{
		if (empty($this->unitList)) {
			return false;
		}
		return array_keys($this->unitList);
	}
	
	
	/**
	 * Get all unit IDs that appear after a unit (including the specified unit ID).
	 * 
	 * @param $unitIDToCheck The ID of the unit to use as the marker.
	 * @return Array The list IDs for units that appear after this unit.
	 */
	public function getUnitIDList_afterUnit($unitIDToCheck)
	{
		// Not got any units, or it's not in the list.
		if (empty($this->unitList) || !isset($this->unitList[$unitIDToCheck])) {
			return false;
		}
		
		$sublist = array();
		$foundIt = false;
		foreach ($this->unitList as $unitID => $unitDetails)
		{
			// Found it.
			if ($unitID == $unitIDToCheck) { 	
				$foundIt = true; 
			}
			
			// Add remaining items. (this will also add the current item)
			if ($foundIt) {
				$sublist[] = $unitID;
			}
		}
		
		return $sublist;
	}
	
	/**
	 * Get all unit IDs that appear after a module (including the specified module ID).
	 * 
	 * @param $moduleIDToCheck The ID of the module to use as the marker.
	 * @return Array The list IDs for units that appear after this module.
	 */
	public function getUnitIDList_afterModule($moduleIDToCheck)
	{
		// Not got any modules, or it's not in the list.
		if (empty($this->completeList) || !isset($this->completeList[$moduleIDToCheck])) {
			return false;
		}
		
		$currentModule = $this->completeList[$moduleIDToCheck];
		
		// Module has units, so that's fine, just pick off the first one in the module
		if (!empty($currentModule['units'])) 
		{
			// Get the first item in the list
			reset($currentModule['units']);
			$firstUnit = current($currentModule['units']);
			
			// Use this unit to return the list.
			return $this->getUnitIDList_afterUnit($firstUnit->unit_id);
		}
		
		// Module has no units, so check previous module.
		// Postponed - decided not to add support for modules that have no units for now.		
		
		return false;
	}
}

?>