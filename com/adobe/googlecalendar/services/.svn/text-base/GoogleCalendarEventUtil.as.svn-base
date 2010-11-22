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
package com.adobe.googlecalendar.services
{
	import com.adobe.googlecalendar.errors.InvalidArgumentsError;
	
	public class GoogleCalendarEventUtil
	{ 
		public static const GOOGLE_ATOM_NS:String = "http://www.w3.org/2005/Atom";
		public static const GOOGLE_DATA_API_NS:String = "http://schemas.google.com/g/2005";
		public static const GOOGLE_CALENDAR_NS:String = "http://schemas.google.com/gCal/2005";
		
		public static const TEXT_CONTENT_TYPE:String = "text";
		
		public function GoogleCalendarEventUtil()
		{
		}

		public static function get CATEGORY_SCHEMA():String
		{
			return GOOGLE_DATA_API_NS + "#kind";
		}
		
		public static function get CATEGORY_TERM():String
		{
			return GOOGLE_DATA_API_NS + "#event";
		}
		
		public static function get STATUS_CANCELED():String
		{
			return GOOGLE_DATA_API_NS + "#event.canceled";
		}
		
		public static function get STATUS_TENTATIVE():String
		{
			return GOOGLE_DATA_API_NS + "#event.tentative";
		}
		
		public static function get STATUS_CONFIRMED():String
		{
			return GOOGLE_DATA_API_NS + "#event.confirmed";
		}
		
		public static function get VISIBILITY_CONFIDENTIAL():String
		{
			return GOOGLE_DATA_API_NS + "#event.confidential";
		}
		
		public static function get VISIBILITY_DEFAULT():String
		{
			return GOOGLE_DATA_API_NS + "#event.default";
		}
		
		public static function get VISIBILITY_PRIVATE():String
		{
			return GOOGLE_DATA_API_NS + "#event.private";
		}
		
		public static function get VISIBILITY_PUBLIC():String
		{
			return GOOGLE_DATA_API_NS + "#event.public";
		}
		
		public static function get TRANSPARENCY_OPAQUE():String
		{
			return GOOGLE_DATA_API_NS + "#event.opaque";
		}
		
		public static function get TRANSPARENCY_TRANSPERANT():String
		{
			return GOOGLE_DATA_API_NS + "#event.transparent";
		}
		
		public static function get WHERE_DEFAULT_REL():String
		{
			return GOOGLE_DATA_API_NS + "#event";
		}
		
		///////util functions//////////
		
		public static function createWhenObject(
		startDateTime:Date, 
		endDateTime:Date, 
		isFullDay:Boolean):Object
		{
			var when:Object = new Object();
			startDateTime = startDateTime != null ? startDateTime : new Date();
			endDateTime = endDateTime != null ? endDateTime : startDateTime;
			
			if(endDateTime.getTime() < startDateTime.getTime())
			{
				throw new InvalidArgumentsError("End date cannot be before start date");
			}
			
			var startDateTimeStr:String = startDateTime.fullYearUTC + "-" + 
			(startDateTime.monthUTC + 1) + "-" + 
			(startDateTime.dateUTC < 10 ? "0" + startDateTime.dateUTC:startDateTime.dateUTC);
			
			var endDateTimeStr:String = endDateTime.fullYearUTC + "-" + 
			(endDateTime.monthUTC + 1) + "-" + 
			(endDateTime.dateUTC < 10 ? "0" + endDateTime.dateUTC:endDateTime.dateUTC);
			
			if(!isFullDay)
			{
				var startTimeStr:String = (startDateTime.hoursUTC < 10? "0" + startDateTime.hoursUTC:startDateTime.hoursUTC) + ":" +
				(startDateTime.minutesUTC < 10? "0" + startDateTime.minutesUTC:startDateTime.minutesUTC) + ":" +
				(startDateTime.secondsUTC < 10? "0" + startDateTime.secondsUTC:startDateTime.secondsUTC);
				
				var endTimeStr:String = (endDateTime.hoursUTC < 10? "0" + endDateTime.hoursUTC:endDateTime.hoursUTC) + ":" +
				(endDateTime.minutesUTC < 10? "0" + endDateTime.minutesUTC:endDateTime.minutesUTC) + ":" +
				(endDateTime.secondsUTC < 10? "0" + endDateTime.secondsUTC:endDateTime.secondsUTC);	
				
				startDateTimeStr += "T" + startTimeStr;
				endDateTimeStr += "T" + endTimeStr;											
			}
			
			when['startTime'] = startDateTimeStr;
			when['endTime'] = endDateTimeStr;
			
			return when;
		}
		
		public static function getNowAsString():String
		{
			var dateTimeStr:String = null;
			var now:Date = new Date();

			dateTimeStr = now.fullYearUTC + "-" + 
			((now.monthUTC + 1) < 10 ? "0" + (now.monthUTC + 1):(now.monthUTC + 1)) + "-" + 
			(now.dateUTC < 10 ? "0" + now.dateUTC:now.dateUTC);

			var timeStr:String = (now.hoursUTC < 10? "0" + now.hoursUTC:now.hoursUTC) + ":" +
			(now.minutesUTC < 10? "0" + now.minutesUTC:now.minutesUTC) + ":" +
			(now.secondsUTC < 10? "0" + now.secondsUTC:now.secondsUTC);
							
			dateTimeStr += "T" + timeStr;			
			return dateTimeStr;
		}
	}
}