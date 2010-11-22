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
	 * This VO represents a user of this application
	 * This is not specific to GoogleCalendar and definitely can be moved
	 * out of this package
	 */
	public class UserVO
	{
		private var _userName:String;
		private var _userPassword:String;
		private var _authenticated:Boolean;
		private var _loggedInTime:Date;
		
		public function UserVO()
		{
		}

		public function get userName():String
		{
			return this._userName;
		}
		
		public function set userName(value:String):void
		{
			this._userName = value;
		}
		
		public function get userPassword():String
		{
			return this._userPassword;
		}
		
		public function set userPassword(value:String):void
		{
			this._userPassword = value;
		}
		
		public function get authenticated():Boolean
		{
			return this._authenticated;
		}
		
		public function set authenticated(value:Boolean):void
		{
			this._authenticated = value;
		}
		
		public function get loggedInTime():Date
		{
			return this._loggedInTime;
		}
		
		public function set loggedInTime(value:Date):void
		{
			this._loggedInTime = value;
		}
	}
}