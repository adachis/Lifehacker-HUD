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
package com.adobe.googlecalendar.valueobjects
{
	/**
	 * This VO represents period of time
	 * Schema: http://code.google.com/apis/gdata/elements.html#gdWhen
	 */
	import mx.collections.ArrayCollection;
	
	public class WhenVO
	{
		public var startTime:String;
		public var endTime:String;
		public var valueString:String;
		
		//will contain ReminderVO objects
		public var reminders:ArrayCollection;
		
		public function WhenVO()
		{
		}
		
		public function addReminder(
		days:int, 
		hours:int, 
		minutes:int,
		method:String = "alert"):ReminderVO
		{
			var eventReminder:ReminderVO = new ReminderVO();
			//looks like we can set only one of these
			//so, converting everything to minutes
			
			var totalTime:int = (days * 24 * 60) + (hours * 60) + minutes;
			//eventReminder.days = String(days);
			//eventReminder.hours = String(hours);
			eventReminder.minutes = String(totalTime);
			eventReminder.method = String(method);
			
			if(reminders == null)
			{
				reminders = new ArrayCollection();
			}
			
			if(eventReminder != null)
			{
				reminders.addItem(eventReminder);
			}
			return eventReminder;
		}

	}
}