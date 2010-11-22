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
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarUserVO;

	public class GoogleCalendarAuthenticatorEvent extends AbstractCalendarEvent
	{
		public static const AUTHENTICATION_RESPONSE:String = "com.adobe.googlecalendar.events.GoogleCalendarAuthenticatorEvent.Authentication_Response";
		public static const AUTHENTICATION_FAULT:String = "com.adobe.googlecalendar.events.GoogleCalendarAuthenticatorEvent.AUTHENTICATION_FAULT";
		
		
		public var authenticatedUser:GoogleCalendarUserVO;
		
		public function GoogleCalendarAuthenticatorEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
		
	}
}