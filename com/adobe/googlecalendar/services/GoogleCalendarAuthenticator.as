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
	import com.adobe.googlecalendar.events.GoogleCalendarAuthenticatorEvent;
	import com.adobe.googlecalendar.model.GoogleCalendarModelLocator;
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarUserVO;
	
	import mx.rpc.AsyncToken;
	import mx.rpc.Responder;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.HTTPService;
	
	public class GoogleCalendarAuthenticator extends AbstractCalendarService
	{
		private const AUTHENTICATION_URL:String = "https://www.google.com/accounts/ClientLogin";
		private const GOOGLE_SERVICE_ID:String = "cl";
		
		private const PARAMS_USER_NAME:String = "Email";
		private const PARAMS_USER_PASSWORD:String = "Passwd";
		private const PARAMS_APPLICATION_ID:String  = "source";
		private const PARAMS_SERVICE_ID:String = "service";
		
		private var _applicationId:String;
		private var _userName:String;
		private var _userPassword:String;
		private var _authenticationToken:String = null;
		
		private var authenticationService:HTTPService
		public function GoogleCalendarAuthenticator()
		{
			authenticationService = new HTTPService();
		}

		/**
		 * Invoke this function to authenticate user with google server
		 * Listen to the AuthenticationCompleteEvent.AUTHENTICATION_RESPONSE
		 * and check for the GoogleCalendarModelLocator.authenticatedUser
		 */
		public function authenticateUser(
		userName:String, 
		userPassword:String, 
		applicationId:String = "sample-application"):void
		{
			_applicationId = applicationId != null ? applicationId:"sample-application";
			_userName = userName != null?userName:"";
			_userPassword = userPassword != null?userPassword:"";
			
			//reset authentication token
			_authenticationToken = null;
			internalAuthenticateUser();
		}
		
		private function internalAuthenticateUser():void
		{
			if(_userName != null && _userPassword != null)
			{
				logMessage("Sending authentication request", LOG_LEVEL_INFORMATION);
				
				authenticationService.url = AUTHENTICATION_URL;
				authenticationService.method = "POST";
				authenticationService.resultFormat = HTTPService.RESULT_FORMAT_TEXT;
							
				var params:Object = new Object();
				params[PARAMS_APPLICATION_ID] = _applicationId;
				params[PARAMS_SERVICE_ID] = GOOGLE_SERVICE_ID;
				params[PARAMS_USER_NAME] = _userName;
				params[PARAMS_USER_PASSWORD] = _userPassword;
				
				var token:AsyncToken = authenticationService.send(params);
				token.addResponder(new Responder(handleAuthenticationResult, handleAuthenticationFault));

				logMessage("Authentication request sent",LOG_LEVEL_INFORMATION);					
			}
		}
		
		private function handleAuthenticationResult(event:ResultEvent):void
		{
			logMessage("Authentication response recieved",LOG_LEVEL_INFORMATION);
			
			if(event.result != null && event.result is String)
			{
				var resultStr:String = event.result as String;
				var resultArray:Array = resultStr.split("\n");
				var authStr:String;
				var authArray:Array;
				
				if(resultArray != null && resultArray.length > 0)
				{
					for(var i:int = 0 ; i < resultArray.length; i++)
					{
						if(resultArray[i] != null)
						{
							authStr = resultArray[i];
							authArray = authStr.split("=");
							if(authArray != null && authArray.length > 0)
							{
								if(authArray[0] == "Auth")
								{
									_authenticationToken = authArray[1];
								}									
							}							
						}
					}
				}
			}
			
			logMessage("Parsed response",LOG_LEVEL_INFORMATION);

							
			var authenticatedUser:GoogleCalendarUserVO = new GoogleCalendarUserVO();
			authenticatedUser.userName = _userName;
			authenticatedUser.userPassword = _userPassword;
			authenticatedUser.authenticationToken = _authenticationToken;
			authenticatedUser.loggedInTime = new Date();

			var authEvent:GoogleCalendarAuthenticatorEvent;
			
			//authentication successful
			if(_authenticationToken != null)
			{
				logMessage("Authentication successful",LOG_LEVEL_INFORMATION);
				authEvent = new GoogleCalendarAuthenticatorEvent(
				GoogleCalendarAuthenticatorEvent.AUTHENTICATION_RESPONSE);
		
				authenticatedUser.authenticated = true;
			}
			else
			{
				logMessage("Authentication token missing", LOG_LEVEL_INFORMATION);
				authEvent = new GoogleCalendarAuthenticatorEvent(
				GoogleCalendarAuthenticatorEvent.AUTHENTICATION_FAULT);
				authEvent.errorMessage = "Authentication token missing";
				
				authenticatedUser.authenticated = false;
			}	
			
			if(authEvent != null)
			{
				authEvent.authenticatedUser = authenticatedUser;
				//dispatch event
				dispatchEvent(authEvent);
				
				logMessage("Authentication response event dispatched",LOG_LEVEL_INFORMATION);							
			}
			else
			{
				logMessage("Authentication response event couldn't be dispatched",LOG_LEVEL_INFORMATION);
			}
		} 
		
		private function handleAuthenticationFault(event:FaultEvent):void
		{
			logMessage("Server request to authentication invoked fault event. " + 
					"This can be because of authentication failure also",
			LOG_LEVEL_INFORMATION);
						
			//we will be dispatching authentication failure event for any error
			//cause we don't have the error code as of now
			//we will try to implement this later
			var authenticatedUser:GoogleCalendarUserVO = new GoogleCalendarUserVO();
			authenticatedUser.userName = _userName;
			authenticatedUser.userPassword = _userPassword;
			authenticatedUser.authenticationToken = _authenticationToken;
			authenticatedUser.loggedInTime = new Date();			
			authenticatedUser.authenticated = false;
			
			//dispatch event
			var authEvent:GoogleCalendarAuthenticatorEvent = new GoogleCalendarAuthenticatorEvent(
			GoogleCalendarAuthenticatorEvent.AUTHENTICATION_FAULT);
			authEvent.authenticatedUser = authenticatedUser;
			//ideally we shud be checking for the status code
			authEvent.errorMessage = "Authentication failed";
			
			authEvent.additionalInformation = event.fault.faultString;
			dispatchEvent(authEvent);						
		}
				
	}
}