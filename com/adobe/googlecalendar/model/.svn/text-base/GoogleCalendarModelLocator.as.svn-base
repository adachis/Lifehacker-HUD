/*
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is the as3googlecalendarlib.
 *
 * The Initial Developer of the Original Code is
 * Sujit Reddy G (http://sujitreddyg.wordpress.com/).
 * Portions created by the Initial Developer are Copyright (C) 2008
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
*/
package com.adobe.googlecalendar.model
{
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarUserVO;
	
	import mx.collections.ArrayCollection;
	
	public class GoogleCalendarModelLocator
	{
	
		//instance of authenticated user
		//public var authenticatedUser:GoogleCalendarUserVO;
		
		//ArrayCollection containing all GoogleCalendar objects
		//[Bindable]public var allCalendars:ArrayCollection;
		
		//ArrayCollection containing owned GoogleCalendar objects
		//[Bindable]public var ownedCalendars:ArrayCollection;
		
		//ArrayCollection containing events for the current call
		//contains object of GoogleCalendarEvent type
		//[Bindable] public var currentEvents:ArrayCollection;
				
		private static var _modelLocator:GoogleCalendarModelLocator;
		
		public function GoogleCalendarModelLocator()
		{
		}

		public static function getInstance():GoogleCalendarModelLocator
		{
			if(_modelLocator == null)
			{
				_modelLocator = new GoogleCalendarModelLocator();
			}
			return _modelLocator;
		}
	}
}