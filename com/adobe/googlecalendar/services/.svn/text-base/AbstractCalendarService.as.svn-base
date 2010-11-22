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
	import com.adobe.logging.CalendarLogger;
	
	import flash.events.EventDispatcher;
	
	public class AbstractCalendarService extends EventDispatcher
	{
		//getting these here just to make the loggin easier 
		//and to keep the code simple and small :)
		public static const LOG_LEVEL_ERROR:String = CalendarLogger.LEVEL_ERROR;
		public static const LOG_LEVEL_WARNING:String = CalendarLogger.LEVEL_WARNING;
		public static const LOG_LEVEL_INFORMATION:String = CalendarLogger.LEVEL_INFORMATION;
		public static const LOG_LEVEL_DEBUG:String = CalendarLogger.LEVEL_DEBUG;
		
		public function AbstractCalendarService()
		{
		}

		public function logMessage(logMessage:String, logLevel:String):void
		{
			CalendarLogger.logMessage(logMessage,logLevel);						
		}
	}
}