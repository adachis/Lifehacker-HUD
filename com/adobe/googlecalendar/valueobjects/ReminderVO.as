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
	import com.adobe.CalendarUtil;
	import com.adobe.googlecalendar.errors.InvalidArgumentsError;
	
	/**
	 * Schema: http://code.google.com/apis/gdata/elements.html#gdReminder
	 */
	public class ReminderVO
	{
		public static const ALERT_METHOD_SMS:String = "sms";
		public static const ALERT_METHOD_EMAIL:String = "email";
		public static const ALERT_METHOD_ALERT:String = "alert";
		
		public var absoluteTime:String;
		private var _method:String;
		private var _days:String;
		private var _hours:String;
		private var _minutes:String;
		
		public function set method(value:String):void
		{
			if(value ==ALERT_METHOD_EMAIL || 
			value == ALERT_METHOD_SMS || 
			value == ALERT_METHOD_ALERT)
			{
				this._method = value;
			}
			else
			{
				throw new InvalidArgumentsError("Allowed values are email/alert/sms");
			}
		}
		
		public function get method():String
		{
			return this._method;
		}
		
		public function set days(value:String):void
		{
			if(CalendarUtil.isValidNumber(value))
			{
				this._days = value;
			}
			else
			{
				throw new InvalidArgumentsError("Not a valid number");
			}
		}
		
		public function get days():String
		{
			return this._days;
		}
		
		public function set hours(value:String):void
		{
			if(CalendarUtil.isValidNumber(value))
			{
				this._hours = value;
			}
			else
			{
				throw new InvalidArgumentsError("Not a valid number");
			}			
		}
		
		public function get hours():String
		{
			return this._hours
		}
		
		public function set minutes(value:String):void
		{
			if(CalendarUtil.isValidNumber(value))
			{
				this._minutes = value;
			}
			else
			{
				throw new InvalidArgumentsError("Not a valid number");
			}
		}
		
		public function get minutes():String
		{
			return this._minutes;
		}
		
		public function ReminderVO()
		{
		}
	}
}