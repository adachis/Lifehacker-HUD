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

package com.adobe.extended
{
	import flash.net.URLLoader;
	import flash.net.URLRequest;

	public class CustomURLLoader extends URLLoader
	{
		private var _httpStatusCode:int;
		
		public function CustomURLLoader(request:URLRequest=null)
		{
			super(request);
		}
		
		public function get statusCode():int
		{
			return this._httpStatusCode;
		}
		
		public function set statusCode(val:int):void
		{
			this._httpStatusCode = val;
		}
	}
}