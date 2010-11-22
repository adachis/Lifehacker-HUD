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
	import com.adobe.extended.CustomURLLoader;
	import com.adobe.googlecalendar.errors.IllegalStateError;
	import com.adobe.googlecalendar.errors.InvalidArgumentsError;
	import com.adobe.googlecalendar.events.GoogleCalendarServiceEvent;
	import com.adobe.googlecalendar.model.GoogleCalendarModelLocator;
	import com.adobe.googlecalendar.valueobjects.AuthorVO;
	import com.adobe.googlecalendar.valueobjects.EntryLinkVO;
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarUserVO;
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarVO;
	import com.adobe.googlecalendar.valueobjects.LinkVO;
	import com.adobe.googlecalendar.valueobjects.SummaryVO;
	import com.adobe.googlecalendar.valueobjects.TitleVO;
	import com.adobe.googlecalendar.valueobjects.WhereVO;
	
	import flash.events.Event;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import flash.net.URLRequestHeader;
	import flash.xml.XMLDocument;
	
	import mx.collections.ArrayCollection;
	import mx.collections.XMLListCollection;
	import mx.rpc.AsyncToken;
	import mx.rpc.Responder;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.HTTPService;
	import mx.rpc.xml.SimpleXMLDecoder;
	import mx.utils.ObjectProxy;
	
	public class GoogleCalendarService extends AbstractCalendarService
	{
		private static const ALL_CALENDARS_URL:String = "http://www.google.com/calendar/feeds/default/allcalendars/full";
		private static const OWNED_CALENDARS_URL:String = "http://www.google.com/calendar/feeds/default/owncalendars/full";
		
		
		private var calendarService:HTTPService;
		
		public function GoogleCalendarService()
		{
			super();
			calendarService = new HTTPService();
			calendarService.method = "POST";
			calendarService.resultFormat = HTTPService.RESULT_FORMAT_OBJECT; 
		}
		
		public function getAllCalendars(user:GoogleCalendarUserVO):void
		{
			if(user == null)
			{
				throw new InvalidArgumentsError();
			}
			logMessage("Sending request for all calendars to url " + ALL_CALENDARS_URL, 
			LOG_LEVEL_INFORMATION);
			
			calendarService.url = ALL_CALENDARS_URL;
			calendarService.headers["Authorization"] = "GoogleLogin auth=" + user.authenticationToken;
			calendarService.headers["GData-Version"] = "2";
			
			var token:AsyncToken = calendarService.send();
			token.addResponder(new Responder(onAllCalendarsResponse, onAllCalendarsFault));
			logMessage("Sent request for all calendars to url " + ALL_CALENDARS_URL, 
			LOG_LEVEL_DEBUG);			
		}
		
		private function onAllCalendarsResponse(event:ResultEvent):void
		{
			logMessage("Received success response for get all calendars", 
			LOG_LEVEL_DEBUG);
			
			if(event.result != null)
			{
				var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
				GoogleCalendarServiceEvent.GET_ALL_CALENDARS_RESPONSE);

				calEvent.allCalendars = createCalendarObjects(event.result.feed);
				logMessage("Parsed response and created calendar objects", 
				LOG_LEVEL_DEBUG);

				dispatchEvent(calEvent);
				logMessage("Dispatched GoogleCalendarServiceEvent.GET_ALL_CALENDARS_RESPONSE event", 
				LOG_LEVEL_DEBUG);				
			}			
		}
		
		private function onAllCalendarsFault(event:FaultEvent):void
		{
			logMessage("Get all calendars error " + event.fault.faultString, 
			LOG_LEVEL_DEBUG);				
			
			var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
			GoogleCalendarServiceEvent.GET_ALL_CALENDARS_FAULT);
			calEvent.errorMessage = event.fault.faultString;
			dispatchEvent(calEvent);			

			logMessage("Dispatched GoogleCalendarServiceEvent.GET_ALL_CALENDARS_FAULT event", 
			LOG_LEVEL_DEBUG);							
		}
				
		public function getOwnedCalendars(user:GoogleCalendarUserVO):void
		{
			if(user == null)
			{
				throw new InvalidArgumentsError();
			}
			
			logMessage("Sending request for owned calendars to url " + OWNED_CALENDARS_URL, 
			LOG_LEVEL_INFORMATION);
			
			calendarService.url = OWNED_CALENDARS_URL;
			
			calendarService.headers["Authorization"] = "GoogleLogin auth=" + user.authenticationToken;
			calendarService.headers["GData-Version"] = "2";
			
			var token:AsyncToken = calendarService.send();
			token.addResponder(new Responder(onOwnedCalendarsResponse, onOwnedCalendarsFault));
			
			logMessage("Sent request for owned calendars to url " + OWNED_CALENDARS_URL, 
			LOG_LEVEL_DEBUG);			
		}

		private function onOwnedCalendarsResponse(event:ResultEvent):void
		{
			logMessage("Received success response for get owned calendars", 
			LOG_LEVEL_DEBUG);
			
			if(event.result != null)
			{
				var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
				GoogleCalendarServiceEvent.GET_OWNED_CALENDARS_RESPONSE);

				calEvent.ownedCalendars = createCalendarObjects(event.result.feed);
				
				logMessage("Parsed response and created calendar objects", 
				LOG_LEVEL_DEBUG);

				dispatchEvent(calEvent);
				
				logMessage("Dispatched GoogleCalendarServiceEvent.GET_OWNED_CALENDARS_RESPONSE event", 
				LOG_LEVEL_DEBUG);				
			}			
		}
		
		private function onOwnedCalendarsFault(event:FaultEvent):void
		{
			logMessage("Get owned calendars error " + event.fault.faultString, 
			LOG_LEVEL_DEBUG);				
			
			var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
			GoogleCalendarServiceEvent.GET_OWNED_CALENDARS_FAULT);
			calEvent.errorMessage = event.fault.faultString;
			
			dispatchEvent(calEvent);			

			logMessage("Dispatched GoogleCalendarServiceEvent.GET_ALL_OWNED_FAULT event", 
			LOG_LEVEL_DEBUG);							
		}

		private function createCalendarObjects(feedObject:Object):ArrayCollection
		{
			var calendars:ArrayCollection = new ArrayCollection();
			if(feedObject != null && 
			feedObject.hasOwnProperty("entry") && 
			feedObject.entry != null)
			{				
				var calendar:GoogleCalendarVO;
			
				if(feedObject.entry is ArrayCollection)
				{
					var entries:ArrayCollection = feedObject.entry as ArrayCollection;
					for each(var entry:Object in entries)
					{
						calendar = createCalendarObject(entry);
						if(calendar != null)
						{
							calendars.addItem(calendar);
						}
					}					
				}	
				else if(feedObject.entry is ObjectProxy)
				{
					calendar = createCalendarObject(entry);
					if(calendar != null)
					{
						calendars.addItem(calendar);
					}
				}
			}
			logMessage("Calendars created " + calendars, 
			LOG_LEVEL_DEBUG);
						
			return calendars;
		}
		
		private function createCalendarObject(entry:Object):GoogleCalendarVO
		{
			var calendar:GoogleCalendarVO;
			if(entry != null)
			{
				try
				{
					calendar = new GoogleCalendarVO();
					calendar.id = entry.id;
					calendar.accessLevel = entry.accesslevel != null ? entry.accesslevel.value:null;
					calendar.author = createAuthorObject(entry.author);
					calendar.color = entry.color != null ? entry.color.value:null;
					calendar.hidden = entry.hidden != null ? String(entry.hidden.value) == "true"?true:false:false;
					calendar.links = createLinkCollection(entry.link);
					calendar.published = entry.published;
					calendar.selected = entry.selected != null ? String(entry.selected.value) == "true"?true:false:false;
					calendar.summary = createSummaryObject(entry.summary);
					calendar.timeZone = entry.timezone != null ? entry.timezone.value:null;
					calendar.title = createTitleObject(entry.title);
					calendar.updated = entry.updated;
					calendar.where = createWhereCollection(entry.where);													
				}
				catch(e:Error)
				{
					//nothing much to do. just continue
					logMessage(e.message, LOG_LEVEL_ERROR);
				}
			}
			return calendar;			
		}
		
		public function addCalendar(
		calendar:GoogleCalendarVO, 
		user:GoogleCalendarUserVO):void
		{
			if(calendar == null || 
			user == null)
			{
				logMessage("Invalid arguments",LOG_LEVEL_ERROR);
				throw new InvalidArgumentsError();
			}
			
			var addCalXml:String = createAddCalendarXML(calendar);
			
			if(addCalXml != null)
			{
				logMessage("Add calendar XML created",LOG_LEVEL_DEBUG);
				
				var urlRequest:URLRequest = new URLRequest(OWNED_CALENDARS_URL);
				var calendarLoader:CustomURLLoader = new CustomURLLoader();
				
				var authHeaderObj:URLRequestHeader = new URLRequestHeader("Authorization","GoogleLogin auth=" 
				+ user.authenticationToken);
				
				var versionHeaderObj:URLRequestHeader = new URLRequestHeader("GData-Version","2");
				
				urlRequest.requestHeaders.push(authHeaderObj);
				urlRequest.requestHeaders.push(versionHeaderObj);
				
				urlRequest.contentType = "application/atom+xml";
				urlRequest.method = "POST";
				urlRequest.data = addCalXml;

				calendarLoader.addEventListener(Event.COMPLETE, onAddCalResponse);
				calendarLoader.addEventListener(IOErrorEvent.IO_ERROR, onAddCalFault);
				calendarLoader.addEventListener(HTTPStatusEvent.HTTP_STATUS, onAddCalHttpStatus);
				calendarLoader.load(urlRequest);
				
				logMessage("Sent request to add calendar to this URL: " + OWNED_CALENDARS_URL,
				LOG_LEVEL_DEBUG);				
			}
		}
		
		private function onAddCalHttpStatus(event:HTTPStatusEvent):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("HTTPStatus event for adding calendar invoked: " + event.status,
				LOG_LEVEL_DEBUG);
				
				var urlLoader:CustomURLLoader = event.target as CustomURLLoader;
				urlLoader.statusCode = event.status;
			}
		}
		
		private function onAddCalResponse(event:Event):void
		{
			logMessage("Result event for add calendar invoked",LOG_LEVEL_DEBUG);
			
			if(event.target is CustomURLLoader)
			{
				var addCalLoader:CustomURLLoader = event.target as CustomURLLoader;
				
				if(addCalLoader != null && addCalLoader.data != null)
				{
					var addCalEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
					GoogleCalendarServiceEvent.ADD_CALENDAR_RESPONSE);
					
					var decoder:SimpleXMLDecoder = new SimpleXMLDecoder(true);
					var calXML:XMLDocument = new XMLDocument(addCalLoader.data);
					var calObj:Object = decoder.decodeXML(calXML.firstChild);
					
					var calendar:GoogleCalendarVO = createCalendarObject(calObj);
					
					addCalEvent.returnedCalendar = calendar;
					
					dispatchEvent(addCalEvent);
					
					logMessage("Dispatched GoogleCalendarServiceEvent.ADD_CALENDAR_RESPONSE event",
					LOG_LEVEL_DEBUG);
				}
			}
		}
		
		private function onAddCalFault(event:IOErrorEvent):void
		{
			logMessage("Fault function of add calendar invoked" + event.text, 
			LOG_LEVEL_DEBUG);				
			
			var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
			GoogleCalendarServiceEvent.ADD_CALENDAR_FAULT);
			calEvent.errorMessage = event.text;
			dispatchEvent(calEvent);			

			logMessage("Dispatched GoogleCalendarServiceEvent.ADD_CALENDAR_FAULT event", 
			LOG_LEVEL_DEBUG);										
		}
		
		private function createAddCalendarXML(calendar:GoogleCalendarVO):String
		{
			var addCalXML:XML = null;
			if(calendar != null)
			{
				addCalXML = <entry></entry>;
				//setting namespaces
				var defaultNs:Namespace = new Namespace(GoogleCalendarEventUtil.GOOGLE_ATOM_NS);
				var gdNs:Namespace = new Namespace("gd",GoogleCalendarEventUtil.GOOGLE_DATA_API_NS);
				var gCalNs:Namespace = new Namespace("gCal",GoogleCalendarEventUtil.GOOGLE_CALENDAR_NS);
				
				addCalXML.setNamespace(defaultNs);
				addCalXML.addNamespace(gdNs);
				addCalXML.addNamespace(gCalNs);
				
				var calTitle:XML = createTitleXML(calendar.title);
				if(calTitle != null)
				{
					addCalXML.appendChild(calTitle);
				}
				
				var calSummary:XML = createSummaryXML(calendar.summary);
				
				if(calSummary != null)
				{
					addCalXML.appendChild(calSummary);
				}
				
				var calTimeZone:XML = <timezone value={calendar.timeZone}/>
				calTimeZone.setNamespace(gCalNs);
				
				var calHidden:XML = <hidden value={calendar.hidden}/>;
				calHidden.setNamespace(gCalNs);
				
				var calColor:XML = <color value={calendar.color}/>;
				calColor.setNamespace(gCalNs);
				
				var whereXMLList:XMLListCollection = createWhereXMLCollection(calendar.where);
				
				if(whereXMLList != null)
				{
					for each(var calWhere:XML in whereXMLList)
					{
						if(calWhere != null)
						{
							addCalXML.appendChild(calWhere);
						}
					}
				}
				
				addCalXML.appendChild(calTimeZone);
				addCalXML.appendChild(calHidden);
				addCalXML.appendChild(calColor);
			}
			return addCalXML != null ? addCalXML.toXMLString():null;
		}
		
		public function updateCalendar(
		calendar:GoogleCalendarVO, 
		user:GoogleCalendarUserVO):void
		{
			if(calendar == null || user == null)
			{
				throw new InvalidArgumentsError();
			}
			
			var updateCalLink:String = getLinkToUpdateCalendar(calendar);
			
			if(updateCalLink == null || updateCalLink.length <= 0)
			{
				throw new IllegalStateError("No URL available to update this calendar");
			}
			
			var updateCalXml:String = createUpdateCalendarXML(calendar);

			if(updateCalXml != null)
			{
				logMessage("Update calendar XML created",LOG_LEVEL_DEBUG);
				
				var urlRequest:URLRequest = new URLRequest(updateCalLink);
				var calendarLoader:CustomURLLoader = new CustomURLLoader();
				
				var authHeaderObj:URLRequestHeader = new URLRequestHeader("Authorization","GoogleLogin auth=" 
				+ user.authenticationToken);
				
				var versionHeaderObj:URLRequestHeader = new URLRequestHeader("GData-Version","2");
				
				urlRequest.requestHeaders.push(authHeaderObj);
				urlRequest.requestHeaders.push(versionHeaderObj);
				
				urlRequest.contentType = "application/atom+xml";
				urlRequest.method = "PUT";
				urlRequest.data = updateCalXml;

				calendarLoader.addEventListener(Event.COMPLETE, onUpdateCalResponse);
				calendarLoader.addEventListener(IOErrorEvent.IO_ERROR, onUpdateCalFault);
				calendarLoader.addEventListener(HTTPStatusEvent.HTTP_STATUS, onUpdateCalHttpStatus);
				calendarLoader.load(urlRequest);
				
				logMessage("Sent request to update calendar to this URL: " + updateCalLink,
				LOG_LEVEL_DEBUG);				
			}						
		}

		private function onUpdateCalHttpStatus(event:HTTPStatusEvent):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("HTTPStatus event for updating calendar invoked: " + event.status,
				LOG_LEVEL_DEBUG);
				
				var urlLoader:CustomURLLoader = event.target as CustomURLLoader;
				urlLoader.statusCode = event.status;
			}
		}
		
		private function onUpdateCalResponse(event:Event):void
		{
			logMessage("Result event for update calendar invoked",LOG_LEVEL_DEBUG);
			
			if(event.target is CustomURLLoader)
			{
				var updateCalLoader:CustomURLLoader = event.target as CustomURLLoader;
				
				if(updateCalLoader != null && updateCalLoader.data != null)
				{
					var updateCalEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
					GoogleCalendarServiceEvent.UPDATE_CALENDAR_RESPONSE);
					
					var decoder:SimpleXMLDecoder = new SimpleXMLDecoder(true);
					var calXML:XMLDocument = new XMLDocument(updateCalLoader.data);
					var calObj:Object = decoder.decodeXML(calXML.firstChild);
					
					var calendar:GoogleCalendarVO = createCalendarObject(calObj);
					
					updateCalEvent.returnedCalendar = calendar;
					
					dispatchEvent(updateCalEvent);
					
					logMessage("Dispatched GoogleCalendarServiceEvent.UPDATE_CALENDAR_RESPONSE event",
					LOG_LEVEL_DEBUG);
				}
			}
		}
		
		private function onUpdateCalFault(event:IOErrorEvent):void
		{
			logMessage("Fault function of update calendar invoked" + event.text, 
			LOG_LEVEL_DEBUG);				
			
			var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
			GoogleCalendarServiceEvent.UPDATE_CALENDAR_FAULT);
			calEvent.errorMessage = event.text;
			dispatchEvent(calEvent);			

			logMessage("Dispatched GoogleCalendarServiceEvent.UPDATE_CALENDAR_FAULT event", 
			LOG_LEVEL_DEBUG);										
		}
		
		private function createUpdateCalendarXML(calendar:GoogleCalendarVO):String
		{
			var updateCalXML:XML = null;
			if(calendar != null)
			{
				updateCalXML = <entry></entry>;

				var defaultNs:Namespace = new Namespace(GoogleCalendarEventUtil.GOOGLE_ATOM_NS);
				var gdNs:Namespace = new Namespace("gd",GoogleCalendarEventUtil.GOOGLE_DATA_API_NS);
				var gCalNs:Namespace = new Namespace("gCal",GoogleCalendarEventUtil.GOOGLE_CALENDAR_NS);
				
				updateCalXML.setNamespace(defaultNs);
				updateCalXML.addNamespace(gdNs);
				updateCalXML.addNamespace(gCalNs);

				var calId:XML = <id>{calendar.id}</id>;				

				var calPublishedDate:XML = <published>{calendar.published}</published>;
				
				var calUpdatedDate:XML = <updated>
				{GoogleCalendarEventUtil.getNowAsString()}
				</updated>;
								
				var calTitle:XML = createTitleXML(calendar.title);
				
				if(calTitle != null)
				{
					updateCalXML.appendChild(calTitle);
				}
				
				var calSummary:XML = createSummaryXML(calendar.summary);
				
				if(calSummary != null)
				{
					updateCalXML.appendChild(calSummary);
				}
											
				var calTimeZone:XML = <timezone value={calendar.timeZone}/>
				calTimeZone.setNamespace(gCalNs);
				
				var calHidden:XML = <hidden value={calendar.hidden}/>;
				calHidden.setNamespace(gCalNs);
				
				var calColor:XML = <color value={calendar.color}/>;
				calColor.setNamespace(gCalNs);
				
				var whereXMLList:XMLListCollection = createWhereXMLCollection(calendar.where);
				
				if(whereXMLList != null)
				{
					for each(var calWhere:XML in whereXMLList)
					{
						if(calWhere != null)
						{
							updateCalXML.appendChild(calWhere);
						}
					}
				}
				
				var calSelected:XML = <selected value={calendar.selected}/>;
				calSelected.setNamespace(gCalNs);
				
				var calAccessLevel:XML = <accesslevel value={calendar.accessLevel}/>;
				
				updateCalXML.appendChild(calId);
				updateCalXML.appendChild(calPublishedDate);
				updateCalXML.appendChild(calUpdatedDate);

				updateCalXML.appendChild(calTimeZone);
				updateCalXML.appendChild(calHidden);
				updateCalXML.appendChild(calColor);
				updateCalXML.appendChild(calSelected);
				updateCalXML.appendChild(calAccessLevel);
				
				var calAuthor:XML = createAuthorXML(calendar.author);
				if(calAuthor != null)
				{
					updateCalXML.appendChild(calAuthor);
				}
				
				var linkXMLList:XMLListCollection = createLinkXMLCollection(calendar.links);
										
				if(linkXMLList != null)
				{
					for each(var calLink:XML in linkXMLList)
					{
						if(calLink != null)
						{
							updateCalXML.appendChild(calLink);
						}						
					}
				}
			}
			return updateCalXML != null ? updateCalXML.toXMLString():null;
		}
		
		private function getLinkToUpdateCalendar(calendar:GoogleCalendarVO):String
		{
			var updateCalUrl:String = null;
			if(calendar != null && calendar.links != null)
			{
				for each(var link:LinkVO in calendar.links)
				{
					if(link != null && link.rel == "edit")
					{
						updateCalUrl = link.href;
						break;
					}
				}
			}
			logMessage("Returning URL for updating calendar " + updateCalUrl,
			LOG_LEVEL_DEBUG);			
			return updateCalUrl;
		}
		
		public function deleteCalendar(calendar:GoogleCalendarVO, user:GoogleCalendarUserVO):void
		{
			if(calendar == null || user == null)
			{
				throw new InvalidArgumentsError();
			}
			
			var deleteCalLink:String = getLinkToDeleteCalendar(calendar);
			
			if(deleteCalLink == null || deleteCalLink.length <= 0)
			{
				throw new IllegalStateError("No URL available to update this calendar");
			}			
			
			var urlRequest:URLRequest = new URLRequest(deleteCalLink);
			var calendarLoader:CustomURLLoader = new CustomURLLoader();
			
			var authHeaderObj:URLRequestHeader = new URLRequestHeader("Authorization","GoogleLogin auth=" 
			+ user.authenticationToken);
			
			var versionHeaderObj:URLRequestHeader = new URLRequestHeader("GData-Version","2");
			
			var ifHeaderObj:URLRequestHeader = new URLRequestHeader("If-Match","*");
			
			urlRequest.requestHeaders.push(authHeaderObj);
			urlRequest.requestHeaders.push(versionHeaderObj);
			urlRequest.requestHeaders.push(ifHeaderObj);
			
			urlRequest.contentType = "application/atom+xml";
			urlRequest.method = "DELETE";

			calendarLoader.addEventListener(Event.COMPLETE, onDeleteCalResponse);
			calendarLoader.addEventListener(IOErrorEvent.IO_ERROR, onDeleteCalFault);
			calendarLoader.addEventListener(HTTPStatusEvent.HTTP_STATUS, onDeleteCalHttpStatus);
			calendarLoader.load(urlRequest);
			
			logMessage("Sent request to delete calendar to this URL: " + deleteCalLink,
			LOG_LEVEL_DEBUG);										
		}	
		
		private function onDeleteCalHttpStatus(event:HTTPStatusEvent):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("HTTPStatus event for deleting a calendar invoked: " + event.status,
				LOG_LEVEL_DEBUG);
				
				var urlLoader:CustomURLLoader = event.target as CustomURLLoader;
				urlLoader.statusCode = event.status;
			}
		}
		
		private function onDeleteCalResponse(event:Event):void
		{
			logMessage("Result event for delete calendar invoked",LOG_LEVEL_DEBUG);
			
			var deleteCalEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
			GoogleCalendarServiceEvent.DELETE_CALENDAR_RESPONSE);
					
			dispatchEvent(deleteCalEvent);
			
			logMessage("Dispatched GoogleCalendarServiceEvent.DELETE_CALENDAR_RESPONSE event", 
			LOG_LEVEL_DEBUG);													
		}
		
		private function onDeleteCalFault(event:IOErrorEvent):void
		{
			logMessage("Fault function of delete calendar invoked " + event.text, 
			LOG_LEVEL_DEBUG);				
			
			var calEvent:GoogleCalendarServiceEvent = new GoogleCalendarServiceEvent(
			GoogleCalendarServiceEvent.DELETE_CALENDAR_FAULT);
			calEvent.errorMessage = event.text;
			dispatchEvent(calEvent);			

			logMessage("Dispatched GoogleCalendarServiceEvent.DELETE_CALENDAR_FAULT event", 
			LOG_LEVEL_DEBUG);										
		}
		
		private function getLinkToDeleteCalendar(calendar:GoogleCalendarVO):String
		{
			var deleteCalLink:String = null;
			if(calendar != null && calendar.links != null)
			{
				for each(var link:LinkVO in calendar.links)
				{
					if(link != null && link.rel == "edit")
					{
						deleteCalLink = link.href;
						break;
					}
				}
			}
			logMessage("Returning URL for deleting calendar " + deleteCalLink,
			LOG_LEVEL_DEBUG);			
			return deleteCalLink;
		}

/////////////////functions to create XML from VO objects/////////////////////

		private function createWhereXMLCollection(whereCollection:ArrayCollection):XMLListCollection
		{
			//TODO need to add support for entry link
			var whereXMLList:XMLListCollection = null;
			var gdNs:Namespace = new Namespace("gd",GoogleCalendarEventUtil.GOOGLE_DATA_API_NS);
			if(whereCollection != null)
			{
				whereXMLList = new XMLListCollection();
				var whereXML:XML;
				
				for each(var where:WhereVO in whereCollection)
				{
					if(where != null)
					{
						whereXML = <where valueString={where.valueString} rel={where.rel} label={where.label}/>
						whereXML.setNamespace(gdNs);
						whereXMLList.addItem(whereXML);							
					}					
				}
			}
			
			return whereXMLList;
		}

		private function createLinkXMLCollection(linkCollection:ArrayCollection):XMLListCollection
		{
			var linkXMLList:XMLListCollection = null;
			
			if(linkCollection != null)
			{
				linkXMLList = new XMLListCollection();
				var linkXML:XML;
				for each(var link:LinkVO in linkCollection)
				{
					if(link != null)
					{
						linkXML = <link rel={link.rel} type={link.type} href={link.href} title={link.title}/>
						linkXMLList.addItem(linkXML);						
					}
				}
			}
			return linkXMLList;
		}

		private function createTitleXML(title:TitleVO):XML
		{
			var titleXML:XML = null;
			if(title != null)
			{
				titleXML = <title 
				type={title.type}>
				{title.title}
				</title>;				
			}			
			return titleXML;
		}

		private function createSummaryXML(summary:SummaryVO):XML
		{
			var summaryXML:XML = null;
			if(summary != null)
			{
				summaryXML = <summary 
				type={summary.type}>
				{summary.summary}
				</summary>;
			}
			return summaryXML;
		}
		
		private function createAuthorXML(author:AuthorVO):XML
		{
			var authorXML:XML = null;
			if(author != null)
			{
				authorXML = <author>
				<name>{author.name}</name>
				<email>{author.email}</email>
				</author>;
			}
			return authorXML;
		}
		
/////////////////functions to create VO object from XML response/////////////////

		private function createLinkCollection(linksObj:Object):ArrayCollection
		{
			var links:ArrayCollection = null;
			var link:LinkVO = null;
			
			if(linksObj != null && linksObj is ArrayCollection)
			{
				links = new ArrayCollection();
				var linkColl:ArrayCollection = linksObj as ArrayCollection;
				
				for each(var linkObj:Object in linkColl)
				{
					if(linkObj != null)
					{
						link = new LinkVO();
						link.href = linkObj.href;
						link.rel = linkObj.rel;
						link.title = linkObj.title;
						link.type = linkObj.type;
						links.addItem(link);
					}
				}
			}
			else if(linksObj != null)
			{
				links = new ArrayCollection();
				link = new LinkVO();
				link.href = linksObj.href;
				link.rel = linksObj.rel;
				link.title = linksObj.title;
				link.type = linksObj.type;
				links.addItem(link);				
			}
			return links;
		}
		
		private function createWhereCollection(whereObj:Object):ArrayCollection
		{
			var whereCollection:ArrayCollection = null;
			var where:WhereVO = null;
			
			if(whereObj != null && whereObj is ArrayCollection)
			{
				whereCollection = new ArrayCollection();
				var whereColl:ArrayCollection = whereObj as ArrayCollection;
				
				for each(var wh:Object in whereColl)
				{
					if(wh != null)
					{
						where = new WhereVO();
						where.label = wh.label;
						where.rel = wh.rel;
						where.valueString = wh.valueString;
						where.entryLink = createEntryLinkObject(wh.entryLink);
						
						whereCollection.addItem(where);						
					}
				}
			}
			else if(whereObj != null)
			{
				whereCollection = new ArrayCollection();
				where = new WhereVO();
				where.label = whereObj.label;
				where.rel = whereObj.rel;
				where.valueString = whereObj.valueString;
				where.entryLink = createEntryLinkObject(whereObj.entryLink);
				
				whereCollection.addItem(where);						
			}
			return whereCollection;
		}
		
		private function createEntryLinkObject(entryLinkObj:Object):EntryLinkVO
		{
			var entryLink:EntryLinkVO = null;
			if(entryLinkObj != null)
			{
				entryLink = new EntryLinkVO();
				entryLink.entry = entryLinkObj.entry;
				entryLink.href = entryLinkObj.href;
				entryLink.readOnly = entryLinkObj.readOnly;
				entryLink.rel = entryLinkObj.rel;
			}
			return entryLink;
		}
		
		private function createAuthorObject(authorObj:Object):AuthorVO
		{
			var author:AuthorVO = null;
			if(authorObj != null)
			{
				author = new AuthorVO();
				author.email = authorObj.email;
				author.name = authorObj.name;
			}
			return author;
		}
		
		private function createSummaryObject(summaryObj:Object):SummaryVO
		{
			var summary:SummaryVO = null;
			if(summaryObj != null && summaryObj is String)
			{
				summary = new SummaryVO();
				summary.summary = String(summaryObj);
				summary.type = GoogleCalendarEventUtil.TEXT_CONTENT_TYPE;
			}
			else if(summaryObj != null)
			{
				summary = new SummaryVO();
				summary.summary = summaryObj.summary;
				summary.type = summaryObj.type;
			}
			return summary;
		}
		
		private function createTitleObject(titleObj:Object):TitleVO
		{
			var title:TitleVO = null;
			if(titleObj != null && titleObj is String)
			{
				title = new TitleVO();
				title.title = String(titleObj);
				title.type = GoogleCalendarEventUtil.TEXT_CONTENT_TYPE;				
			}
			else if(titleObj != null)
			{
				title = new TitleVO();
				title.title = titleObj.title;
				title.type = titleObj.type;
			}
			return title;
		}			
	}
}