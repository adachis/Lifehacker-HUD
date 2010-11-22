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
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarVO;
	
	import mx.collections.ArrayCollection;

	public class GoogleCalendarServiceEvent extends AbstractCalendarEvent
	{
		//events dispatched when getAllEvents function is invoked
		public static const GET_ALL_CALENDARS_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.GET_ALL_CALENDARS_RESPONSE";
		public static const GET_ALL_CALENDARS_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.GET_ALL_CALENDARS_FAULT";
		
		//events dispatched when getOwnedCalendars function is invoked
		public static const GET_OWNED_CALENDARS_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.GET_OWNED_CALENDARS_RESPONSE";
		public static const GET_OWNED_CALENDARS_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.GET_OWNED_CALENDARS_FAULT";

		//events dispatched when addCalendar function is invoked
		public static const ADD_CALENDAR_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.ADD_CALENDAR_RESPONSE";
		public static const ADD_CALENDAR_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.ADD_CALENDAR_FAULT";
		
		//events dispatched when updateCalendar is invoked
		public static const UPDATE_CALENDAR_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.UPDATE_CALENDAR_RESPONSE";
		public static const UPDATE_CALENDAR_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.UPDATE_CALENDAR_FAULT";

		//events dispatched when deleteCalendar function is invoked
		public static const DELETE_CALENDAR_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.DELETE_CALENDAR_RESPONSE";
		public static const DELETE_CALENDAR_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarServiceEvent.DELETE_CALENDAR_FAULT";
		
		public var returnedCalendar:GoogleCalendarVO;
		public var allCalendars:ArrayCollection;
		public var ownedCalendars:ArrayCollection;
		
		public function GoogleCalendarServiceEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
		
	}
}