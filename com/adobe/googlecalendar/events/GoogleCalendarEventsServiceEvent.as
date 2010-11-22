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
package com.adobe.googlecalendar.events
{
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarEventVO;
	
	import mx.collections.ArrayCollection;

	public class GoogleCalendarEventsServiceEvent extends AbstractCalendarEvent
	{
		//events dispatched when getEventsForDateRange() function is invoked
		public static const GET_EVENTS_FOR_DATE_RANGE_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.GET_EVENTS_FOR_DATE_RANGE_RESPONSE";
		public static const GET_EVENTS_FOR_DATE_RANGE_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.GET_EVENTS_FOR_DATE_RANGE_FAULT";
		
		//events dispatched when addEventToCalendar() is invoked
		public static const ADD_EVENT_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.ADD_EVENT_RESPONSE";
		public static const ADD_EVENT_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.ADD_EVENT_FAULT";
		
		//events dispatched when updateEventInCalendar() is invoked
		public static const UPDATE_EVENT_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.UPDATE_EVENT_RESPONSE";
		public static const UPDATE_EVENT_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.UPDATE_EVENT_FAULT";
		
		public static const DELETE_EVENT_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.DELETE_EVENT_RESPONSE";
		public static const DELETE_EVENT_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent.DELETE_EVENT_FAULT";
		
		public var returnedCalendarEvent:GoogleCalendarEventVO;
		//this collection will contain GoogleCalendarEventVO objects
		public var calendarEvents:ArrayCollection;
		
		public function GoogleCalendarEventsServiceEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
		
	}
}