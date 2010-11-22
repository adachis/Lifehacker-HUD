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
	import com.adobe.googlecalendar.events.GoogleCalendarEventsServiceEvent;
	import com.adobe.googlecalendar.model.GoogleCalendarModelLocator;
	import com.adobe.googlecalendar.valueobjects.AuthorVO;
	import com.adobe.googlecalendar.valueobjects.CategoryVO;
	import com.adobe.googlecalendar.valueobjects.CommentsVO;
	import com.adobe.googlecalendar.valueobjects.ContentVO;
	import com.adobe.googlecalendar.valueobjects.EntryLinkVO;
	import com.adobe.googlecalendar.valueobjects.FeedLinkVO;
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarEventVO;
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarUserVO;
	import com.adobe.googlecalendar.valueobjects.GoogleCalendarVO;
	import com.adobe.googlecalendar.valueobjects.LinkVO;
	import com.adobe.googlecalendar.valueobjects.ReminderVO;
	import com.adobe.googlecalendar.valueobjects.TitleVO;
	import com.adobe.googlecalendar.valueobjects.WhenVO;
	import com.adobe.googlecalendar.valueobjects.WhereVO;
	import com.adobe.googlecalendar.valueobjects.WhoVO;
	
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
	
	public class GoogleCalendarEventsService extends AbstractCalendarService
	{
		private var eventsService:HTTPService;
		private var model:GoogleCalendarModelLocator;
		
		public function GoogleCalendarEventsService()
		{
			super();
			eventsService = new HTTPService();
			eventsService.method = "POST";
			eventsService.resultFormat = HTTPService.RESULT_FORMAT_OBJECT;
			
			model = GoogleCalendarModelLocator.getInstance();
		}
		
		public function getEventsForDateRange(calendar:GoogleCalendarVO, 
		user:GoogleCalendarUserVO,
		startDate:Date,
		endDate:Date):void
		{
			if(calendar == null || user == null)
			{
				logMessage("Invalid arguments. calendar - " + 
				calendar + " ,user - " + user,
				LOG_LEVEL_DEBUG);
				throw new InvalidArgumentsError();
			}
			var retrieveEventsUrl:String = 
			getLinkToRetrieveEvents(calendar, startDate, endDate);
			
			if(retrieveEventsUrl == null || retrieveEventsUrl.length <= 0)
			{
				logMessage("This calendar has no location defined to get events",
				LOG_LEVEL_ERROR);
				throw new IllegalStateError(
				"This calendar has no location defined to get events");
			}
			
			eventsService.url = retrieveEventsUrl;
			
			logMessage("Calendar events url " + retrieveEventsUrl,
			LOG_LEVEL_DEBUG);
			
			eventsService.headers["Authorization"] = "GoogleLogin auth=" + user.authenticationToken;
			eventsService.headers["GData-Version"] = "2";
			
			var token:AsyncToken = eventsService.send();
			token.addResponder(new Responder(onGetEventsResponse, onGetEventsFault));
			
			logMessage("Request for getting events sent",
			LOG_LEVEL_INFORMATION);
		}
		
		private function onGetEventsResponse(event:ResultEvent):void
		{
			logMessage("Get Events response function invoked",
			LOG_LEVEL_INFORMATION);
			if(event.result != null)
			{				
				logMessage("Created event objects",LOG_LEVEL_INFORMATION);
				
				var calEvent:GoogleCalendarEventsServiceEvent = 
				new GoogleCalendarEventsServiceEvent(
				GoogleCalendarEventsServiceEvent.GET_EVENTS_FOR_DATE_RANGE_RESPONSE);
				calEvent.calendarEvents = createEventObjects(event.result.feed);
							
				dispatchEvent(calEvent);
				
				logMessage(
				"Dispatched GoogleCalendarEventsServiceEvent." + 
				"GET_EVENTS_FOR_DATE_RANGE_RESPONSE event",
				LOG_LEVEL_INFORMATION);
			}
		}
		
		private function onGetEventsFault(event:FaultEvent):void
		{
			logMessage("Error retrieving events " + event.fault.faultString, 
			LOG_LEVEL_ERROR);
			var calEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
			GoogleCalendarEventsServiceEvent.GET_EVENTS_FOR_DATE_RANGE_FAULT);
			calEvent.errorMessage = event.fault.faultString;
			dispatchEvent(calEvent);
			logMessage("Dispatched GoogleCalendarEventsServiceEvent.GET_EVENTS_FOR_DATE_RANGE_FAULT event",
			LOG_LEVEL_INFORMATION);
		}
		
		private function createEventObjects(feedObject:Object):ArrayCollection
		{
			var events:ArrayCollection = new ArrayCollection();
			if(feedObject != null &&			
			feedObject.hasOwnProperty("entry") && 
			feedObject.entry != null)
			{
				var calEvent:GoogleCalendarEventVO;
				if(feedObject.entry is ArrayCollection)
				{
					var entries:ArrayCollection = feedObject.entry as ArrayCollection;
					
					
					for each(var entry:Object in entries)
					{
						calEvent = createEventObject(entry);
						if(calEvent != null)
						{
							events.addItem(calEvent);
						}
					}					
				}
				else if(feedObject.entry is ObjectProxy)
				{
					calEvent = createEventObject(feedObject.entry);
					if(calEvent != null)
					{
						events.addItem(calEvent);
					}
				}				
			}
			return events;
		}
		
		private function createEventObject(entry:Object):GoogleCalendarEventVO
		{
			var calEvent:GoogleCalendarEventVO;
					if(entry != null)
					{
						try
						{
							calEvent = new GoogleCalendarEventVO();
							calEvent.etag = entry['gd:etag'];
							calEvent.author = createAuthorObject(entry.author);
							calEvent.category = createCategoryCollection(entry.category);
							calEvent.content = createContentObject(entry.content);
							calEvent.eventStatus = entry.eventStatus != null ? entry.eventStatus.value:null;
							calEvent.id = entry.id;
							calEvent.links = createLinkCollection(entry.link);
							calEvent.published = entry.published;
							calEvent.recurrence = entry.recurrence;
							calEvent.sequence = entry.sequence != null ? entry.sequence.value:null;
							calEvent.title = createTitleObject(entry.title);
							calEvent.transparency = entry.transparency != null ? entry.transparency.value:null;
							calEvent.uid = entry.uid;
							calEvent.updated = entry.updated;
							calEvent.visibility = entry.visibility != null ? entry.visibility.value:null;
							calEvent.when = createWhenCollection(entry.when);
							calEvent.where = createWhereCollection(entry.where);
							calEvent.who = createWhoCollection(entry.who);
							calEvent.comments = createCommentsObject(entry.comments);
						}
						catch(e:Error)
						{
							//nothing much to do
							logMessage(e.message, LOG_LEVEL_ERROR);
						}
					}			
			return calEvent			
		}
		
		
		private function getLinkToRetrieveEvents(
		calendar:GoogleCalendarVO, 
		startDate:Date, 
		endDate:Date):String
		{
			var retrieveUrl:String = null
			if(calendar != null && calendar.links != null)
			{
				for each(var link:LinkVO in calendar.links)
				{
					if(link != null && link.rel == "alternate")
					{
						retrieveUrl = link.href;
						break;
					}
				}
			}
			
			if(retrieveUrl != null && retrieveUrl.length > 0)
			{
				//if either start or end date is null 
				//then we are considering current date
				startDate = startDate != null ? startDate : new Date();
				endDate = endDate != null ? endDate : new Date();
				
				var startDateStr:String = startDate.fullYearUTC + "-" + 
				((startDate.monthUTC + 1) < 10 ? "0" + (startDate.monthUTC + 1):(startDate.monthUTC + 1)) + "-" + 
				(startDate.dateUTC < 10 ? "0" + startDate.dateUTC:startDate.dateUTC) + 
				"T00:00:00";
				
				var endDateStr:String = endDate.fullYearUTC + "-" + 
				((endDate.monthUTC + 1) < 10 ? "0" + (endDate.monthUTC + 1):(endDate.monthUTC + 1)) + "-" + 
				(endDate.dateUTC < 10 ? "0" + endDate.dateUTC:endDate.dateUTC) + 
				"T23:59:59";
				
				retrieveUrl += "?start-min=" + startDateStr + "&start-max=" + endDateStr;
			}
			return retrieveUrl;
		}
		
		//////////////Create event///////////////
		
		public function addEventToCalendar(
		calendarEvent:GoogleCalendarEventVO, 
		calendar:GoogleCalendarVO,
		user:GoogleCalendarUserVO):void
		{
			if(calendarEvent == null || calendar == null || user == null)
			{
				logMessage("Invalid arguments. calendarEvent - " + calendarEvent, 
				LOG_LEVEL_DEBUG);
				throw new InvalidArgumentsError();
			}
			
			var createEventUrl:String = getLinkToCreateEvents(calendar);
			
			if(createEventUrl == null || createEventUrl.length <= 0)
			{
				logMessage("This calendar has no location defined to create events",
				LOG_LEVEL_ERROR);
				throw new IllegalStateError(
				"This calendar has no location defined to create events");				
			}
			
			var eventXml:String = createAddEventXML(calendarEvent);

			
			if(eventXml != null)
			{
				logMessage("Created XML to add event",LOG_LEVEL_DEBUG);				

				var urlRequest:URLRequest = new URLRequest(createEventUrl);
				var eventsLoader:CustomURLLoader = new CustomURLLoader();
				
				var authHeaderObj:URLRequestHeader = new URLRequestHeader("Authorization","GoogleLogin auth=" 
				+ user.authenticationToken);
				
				var versionHeaderObj:URLRequestHeader = new URLRequestHeader("GData-Version","2");
				
				urlRequest.requestHeaders.push(authHeaderObj);
				urlRequest.requestHeaders.push(versionHeaderObj);
				
				urlRequest.contentType = "application/atom+xml";
				urlRequest.method = "POST";
				urlRequest.data = eventXml;

				eventsLoader.addEventListener(Event.COMPLETE, onAddCalEventResponse);
				eventsLoader.addEventListener(IOErrorEvent.IO_ERROR, onAddCalEventFault);
				eventsLoader.addEventListener(HTTPStatusEvent.HTTP_STATUS, onAddCalEventHttpStatus);
				eventsLoader.load(urlRequest);

				logMessage("Sent request to add event",LOG_LEVEL_DEBUG);								
			}
		}
		
		private function onAddCalEventHttpStatus(event:HTTPStatusEvent):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("Received HTTP status for add event:" + event.status,LOG_LEVEL_DEBUG);				
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				eventsLoader.statusCode = event.status;
			}
		}
		
		private function onAddCalEventResponse(event:Event):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("Result event for add event invoked",LOG_LEVEL_DEBUG);
							
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				
				if(eventsLoader != null && eventsLoader.data != null)
				{
					var addEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
					GoogleCalendarEventsServiceEvent.ADD_EVENT_RESPONSE);
					
					var decoder:SimpleXMLDecoder = new SimpleXMLDecoder(true);
					var eventXML:XMLDocument = new XMLDocument(eventsLoader.data);
					var eventObj:Object = decoder.decodeXML(eventXML.firstChild);
					
					var calEvent:GoogleCalendarEventVO = createEventObject(eventObj);					
					addEvent.returnedCalendarEvent = calEvent;
					
					dispatchEvent(addEvent);
					
					logMessage("Dispatched GoogleCalendarEventsServiceEvent.ADD_EVENT_RESPONSE",
					LOG_LEVEL_DEBUG);					
				}				
			}
		}
		
		private function onAddCalEventFault(event:IOErrorEvent):void
		{
			logMessage("Fault event for add event invoked",LOG_LEVEL_DEBUG);
			
			var addEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
			GoogleCalendarEventsServiceEvent.ADD_EVENT_FAULT);
			addEvent.errorMessage = event.text;
			dispatchEvent(addEvent);
			
			logMessage("Dispatched GoogleCalendarEventsServiceEvent.ADD_EVENT_FAULT",
			LOG_LEVEL_DEBUG);			
		}
		
		private function createAddEventXML(calendarEvent:GoogleCalendarEventVO):String
		{
			var createEventXML:XML = null;
			if(calendarEvent != null)
			{
				logMessage("Creating XML for adding event",LOG_LEVEL_DEBUG);
								
				createEventXML = <entry></entry>;
				
				//setting namespaces
				var defaultNs:Namespace = new Namespace(GoogleCalendarEventUtil.GOOGLE_ATOM_NS);
				var gdNs:Namespace = new Namespace("gd",GoogleCalendarEventUtil.GOOGLE_DATA_API_NS);
				createEventXML.setNamespace(defaultNs);
				createEventXML.addNamespace(gdNs);
				
				var catXMLList:XMLListCollection = createCategoriesXMLCollection(calendarEvent.category);
				if(catXMLList != null && catXMLList.length > 0)
				{
					for each(var cat:XML in catXMLList)
					{
						if(cat != null)
						{
							createEventXML.appendChild(cat);
						}
					}
				}
				else
				{
					//event category is not set by the developer
					var eventCategory:XML = <category 
					scheme={GoogleCalendarEventUtil.CATEGORY_SCHEMA} 
					term={GoogleCalendarEventUtil.CATEGORY_TERM}>
					</category>;
					createEventXML.appendChild(cat);					
				}
				
				var eventTitle:XML = createTitleXML(calendarEvent.title);
				if(eventTitle != null)
				{
					createEventXML.appendChild(eventTitle);					
				}
				
				var eventContent:XML = createContentXML(calendarEvent.content);
				if(eventContent != null)
				{
					createEventXML.appendChild(eventContent);					
				}
				
				var eventTransparency:XML = <transparency value={calendarEvent.transparency}/>
				eventTransparency.setNamespace(gdNs);
				
				var eventStatus:XML = <eventStatus value={calendarEvent.eventStatus}/>;
				eventStatus.setNamespace(gdNs);
				
				var whereXMLList:XMLListCollection = createWhereXMLCollection(calendarEvent.where);
				if(whereXMLList != null)
				{
					for each(var eventWhere:XML in whereXMLList)
					{
						if(eventWhere != null)
						{
							createEventXML.appendChild(eventWhere);
						}
					}
				}
				
				var whenXMLList:XMLListCollection = createWhenXMLCollection(calendarEvent.when);
				if(whenXMLList != null)
				{			
					for each(var eventWhen:XML in whenXMLList)
					{
						if(eventWhen != null)
						{
							createEventXML.appendChild(eventWhen);
						}
					}
				}
				
				createEventXML.appendChild(eventTransparency);
				createEventXML.appendChild(eventStatus);				
			}
			logMessage("XML for adding event created",LOG_LEVEL_DEBUG);
			
			return createEventXML != null ? createEventXML.toXMLString():null;
		}
		
		
		private function getLinkToCreateEvents(calendar:GoogleCalendarVO):String
		{
			var createEventUrl:String = null
			if(calendar != null && calendar.links != null)
			{
				for each(var link:LinkVO in calendar.links)
				{
					if(link != null && link.rel == "alternate")
					{
						createEventUrl = link.href;
						break;
					}
				}
			}
			logMessage("Returning URL for creating event " + createEventUrl,
			LOG_LEVEL_DEBUG);
			
			return createEventUrl;			
		}
		
		////////////update event/////////
		public function updateEventInCalendar(
		calendarEvent:GoogleCalendarEventVO,  
		user:GoogleCalendarUserVO, 
		forceUpdate:Boolean = false):void
		{
			if(calendarEvent == null ||  
			user == null || 
			calendarEvent.id == null)
			{
				throw new InvalidArgumentsError();
			}
			
			var updateEventUrl:String = getLinkToUpdateEvent(calendarEvent); 
			
			if(updateEventUrl == null || updateEventUrl.length <= 0)
			{
				throw new IllegalStateError("Update URL for calendar is not available");
			}
			
			var updateEventXMLStr:String = createUpdateEventXML(calendarEvent);
			
			if(updateEventXMLStr != null)
			{
				logMessage("Created XML to update event",LOG_LEVEL_DEBUG);				

				var urlRequest:URLRequest = new URLRequest(updateEventUrl);
				var eventsLoader:CustomURLLoader = new CustomURLLoader();
				
				var authHeaderObj:URLRequestHeader = new URLRequestHeader("Authorization","GoogleLogin auth=" 
				+ user.authenticationToken);
				
				var versionHeaderObj:URLRequestHeader = new URLRequestHeader("GData-Version","2");
				
				var ifHeaderObj:URLRequestHeader = new URLRequestHeader("If-Match","*");
				 
				if(calendarEvent.etag != null && forceUpdate == false)
				{
					ifHeaderObj.value = calendarEvent.etag;
				}
				else
				{
					ifHeaderObj.value = "*";
				}
				
				urlRequest.requestHeaders.push(authHeaderObj);
				urlRequest.requestHeaders.push(ifHeaderObj);
				urlRequest.requestHeaders.push(versionHeaderObj);
				
				urlRequest.contentType = "application/atom+xml";
				urlRequest.method = "PUT";
				urlRequest.data = updateEventXMLStr;

				eventsLoader.addEventListener(Event.COMPLETE, onUpdateCalEventResponse);
				eventsLoader.addEventListener(IOErrorEvent.IO_ERROR, onUpdateCalEventFault);
				eventsLoader.addEventListener(HTTPStatusEvent.HTTP_STATUS, onUpdateCalEventHttpStatus);
				eventsLoader.load(urlRequest);

				logMessage("Sent request to update event to URL " + updateEventUrl,LOG_LEVEL_DEBUG);								
			}			
		}
		
		private function onUpdateCalEventHttpStatus(event:HTTPStatusEvent):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("Received HTTP status for update event:" + event.status,LOG_LEVEL_DEBUG);				
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				eventsLoader.statusCode = event.status;
			}
		}
		
		private function onUpdateCalEventResponse(event:Event):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("Result event for update event invoked",LOG_LEVEL_DEBUG);
							
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				
				if(eventsLoader != null && eventsLoader.data != null)
				{
					var updateEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
					GoogleCalendarEventsServiceEvent.UPDATE_EVENT_RESPONSE);

					var decoder:SimpleXMLDecoder = new SimpleXMLDecoder(true);
					var eventXML:XMLDocument = new XMLDocument(eventsLoader.data);
					var eventObj:Object = decoder.decodeXML(eventXML.firstChild);
					var calEvent:GoogleCalendarEventVO = createEventObject(eventObj);
					updateEvent.returnedCalendarEvent = calEvent;
					
					dispatchEvent(updateEvent);
					logMessage("Dispatched GoogleCalendarEventsServiceEvent.UPDATE_EVENT_RESPONSE",
					LOG_LEVEL_DEBUG);					
				}				
			}
		}
		
		private function onUpdateCalEventFault(event:IOErrorEvent):void
		{
			logMessage("Fault event for update event invoked",LOG_LEVEL_DEBUG);
			
			var updateEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
			GoogleCalendarEventsServiceEvent.UPDATE_EVENT_FAULT);
									
			updateEvent.errorMessage = event.text;

			if(event.target != null && event.target is CustomURLLoader)
			{
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				if(eventsLoader.statusCode == 421)
				{
					updateEvent.errorMessage = "Entry has changed on the server since you last retrieved it";
				}
			}
						
			dispatchEvent(updateEvent);
			
			logMessage("Dispatched GoogleCalendarEventsServiceEvent.UPDATE_EVENT_FAULT",
			LOG_LEVEL_DEBUG);			
		}
				
		private function createUpdateEventXML(calendarEvent:GoogleCalendarEventVO):String
		{
			
			var updateEventXML:XML = null;
			if(calendarEvent != null)
			{
				updateEventXML = <entry></entry>;
				//setting namespaces
				var defaultNs:Namespace = new Namespace(GoogleCalendarEventUtil.GOOGLE_ATOM_NS);
				var gdNs:Namespace = new Namespace("gd",GoogleCalendarEventUtil.GOOGLE_DATA_API_NS);
				var gCalNs:Namespace = new Namespace("gCal",GoogleCalendarEventUtil.GOOGLE_CALENDAR_NS);
				
				updateEventXML.setNamespace(defaultNs);
				updateEventXML.addNamespace(gdNs);
				updateEventXML.addNamespace(gCalNs);
				
				var eventId:XML = <id>{calendarEvent.id}</id>;
				
				var eventPubDate:XML = <published>{calendarEvent.published}</published>;
				
				var eventUpdatedDate:XML = <updated>
				{GoogleCalendarEventUtil.getNowAsString()}
				</updated>;
				
				var categoriesXMLList:XMLListCollection = createCategoriesXMLCollection(calendarEvent.category);
				
				if(categoriesXMLList != null)
				{
					for each(var eventCategory:XML in categoriesXMLList)
					{
						if(eventCategory != null)
						{
							updateEventXML.appendChild(eventCategory);				
						}
					}
				}
				
				var eventTitle:XML = createTitleXML(calendarEvent.title);
				if(eventTitle != null)
				{
					updateEventXML.appendChild(eventTitle);					
				}
				
				var eventContent:XML = createContentXML(calendarEvent.content);
				
				if(eventContent != null)
				{
					updateEventXML.appendChild(eventContent);
				}
				
				var eventTransparency:XML = <transparency value={calendarEvent.transparency}/>
				eventTransparency.setNamespace(gdNs);
				
				var eventStatus:XML = <eventStatus value={calendarEvent.eventStatus}/>;
				eventStatus.setNamespace(gdNs);
				
				var whenXMLList:XMLListCollection = createWhenXMLCollection(calendarEvent.when);
				
				if(whenXMLList != null)
				{
					for each(var eventWhen:XML in whenXMLList)
					{
						if(eventWhen != null)
						{
							updateEventXML.appendChild(eventWhen);
						}
					}
				}
				
				var whereXMLList:XMLListCollection = createWhereXMLCollection(calendarEvent.where);
				
				if(whereXMLList != null)
				{
					for each(var eventWhere:XML in whereXMLList)
					{
						if(eventWhere != null)
						{
							updateEventXML.appendChild(eventWhere);
						}
					}
				}
				
				var eventUID:XML = <uid value={calendarEvent.uid}></uid>;
				eventUID.setNamespace(gCalNs);
				
				var eventSequence:XML = <sequence value={calendarEvent.sequence}></sequence>;
				eventSequence.setNamespace(gCalNs);
				
	
				updateEventXML.appendChild(eventId);
				updateEventXML.appendChild(eventPubDate);
				updateEventXML.appendChild(eventUpdatedDate);		
				
				updateEventXML.appendChild(eventTransparency);
				updateEventXML.appendChild(eventStatus);
				updateEventXML.appendChild(eventUID);
				updateEventXML.appendChild(eventSequence);
				
				
				var linkXMLList:XMLListCollection = createLinkXMLCollection(calendarEvent.links);
				
				if(linkXMLList != null)
				{
					for each(var eventLink:XML in linkXMLList)
					{
						if(eventLink != null)
						{
							updateEventXML.appendChild(eventLink);
						}
					}
				}
				
			}
			//TODO doode who is missing :(
			//even the Author tag is missing. looks like the ones not included are not updated 
			//on the server
			return updateEventXML != null ? updateEventXML.toXMLString():null;
		}
		
		private function getLinkToUpdateEvent(calendarEvent:GoogleCalendarEventVO):String
		{
			var updateEventUrl:String = null
			if(calendarEvent != null && calendarEvent.links != null)
			{
				for each(var link:LinkVO in calendarEvent.links)
				{
					if(link != null && link.rel == "edit")
					{
						updateEventUrl = link.href;
						break;
					}
				}
			}
			return updateEventUrl;						
		}
		
		public function deleteEventInCalendar(
		calendarEvent:GoogleCalendarEventVO,
		user:GoogleCalendarUserVO,
		forceDelete:Boolean = false):void
		{
			if(calendarEvent == null ||  
			user == null || 
			calendarEvent.id == null)
			{
				throw new InvalidArgumentsError();
			}
			
			var updateEventUrl:String = getLinkToUpdateEvent(calendarEvent); 
			
			if(updateEventUrl == null || updateEventUrl.length <= 0)
			{
				throw new IllegalStateError("Delete URL for calendar is not available");
			}
			
			var urlRequest:URLRequest = new URLRequest(updateEventUrl);
			var eventsLoader:CustomURLLoader = new CustomURLLoader();
				
			var authHeaderObj:URLRequestHeader = new URLRequestHeader("Authorization","GoogleLogin auth=" 
			+ user.authenticationToken);
				
			var ifHeaderObj:URLRequestHeader = new URLRequestHeader("If-Match","*");
			
			if(calendarEvent.etag != null && forceDelete == false)
			{
				ifHeaderObj.value = calendarEvent.etag;
			}
			else
			{
				ifHeaderObj.value = "*";
			}
			
			var versionHeaderObj:URLRequestHeader = new URLRequestHeader("GData-Version","2");
				 
			urlRequest.requestHeaders.push(authHeaderObj);
			urlRequest.requestHeaders.push(ifHeaderObj);
			urlRequest.requestHeaders.push(versionHeaderObj);
				
			urlRequest.contentType = "application/atom+xml";
			urlRequest.method = "DELETE";

			eventsLoader.addEventListener(Event.COMPLETE, onDeleteCalEventResponse);
			eventsLoader.addEventListener(IOErrorEvent.IO_ERROR, onDeleteCalEventFault);
			eventsLoader.addEventListener(HTTPStatusEvent.HTTP_STATUS, onDeleteCalEventHttpStatus);
			eventsLoader.load(urlRequest);

			logMessage("Sent request to delete event to URL " + updateEventUrl,LOG_LEVEL_DEBUG);								
		}
		
		private function onDeleteCalEventHttpStatus(event:HTTPStatusEvent):void
		{
			if(event.target is CustomURLLoader)
			{
				logMessage("Received HTTP status for delete event:" + event.status,LOG_LEVEL_DEBUG);				
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				eventsLoader.statusCode = event.status;
			}
		}
		
		private function onDeleteCalEventResponse(event:Event):void
		{
			logMessage("Result event for delete event invoked",LOG_LEVEL_DEBUG);
						
			var deleteEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
			GoogleCalendarEventsServiceEvent.DELETE_EVENT_RESPONSE);
			dispatchEvent(deleteEvent);
			logMessage("Dispatched GoogleCalendarEventsServiceEvent.DELETE_EVENT_RESPONSE",
				LOG_LEVEL_DEBUG);				
		}
		
		private function onDeleteCalEventFault(event:IOErrorEvent):void
		{
			logMessage("Fault event for delete event invoked",LOG_LEVEL_DEBUG);
			
			var deleteEvent:GoogleCalendarEventsServiceEvent = new GoogleCalendarEventsServiceEvent(
			GoogleCalendarEventsServiceEvent.DELETE_EVENT_FAULT);
			deleteEvent.errorMessage = event.text;
			
			if(event.target != null && event.target is CustomURLLoader)
			{
				var eventsLoader:CustomURLLoader = event.target as CustomURLLoader;
				if(eventsLoader.statusCode == 421)
				{
					deleteEvent.errorMessage = "Entry has changed on the server since you last retrieved it";
				}
			}
			
			dispatchEvent(deleteEvent);
			
			logMessage("Dispatched GoogleCalendarEventsServiceEvent.DELETE_EVENT_FAULT",
			LOG_LEVEL_DEBUG);			
		}
		
//////////////////////function to create XML from VO////////////////////////////////////////////

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
		
		private function createWhenXMLCollection(whenCollection:ArrayCollection):XMLListCollection
		{
			var whenXMLList:XMLListCollection = null;
			var gdNs:Namespace = new Namespace("gd",GoogleCalendarEventUtil.GOOGLE_DATA_API_NS);
			if(whenCollection != null)
			{
				whenXMLList = new XMLListCollection();
				var whenXML:XML;
				for each(var when:WhenVO in whenCollection)
				{
					if(when != null)
					{
						whenXML = <when 
						startTime={when.startTime}/>
						whenXML.setNamespace(gdNs);
						if(when.endTime != null)
						{
							whenXML.@endTime = when.endTime;
						}
						
						if(when.reminders != null)
						{
							var eventReminder:XML;
							
							for each(var reminder:ReminderVO in when.reminders)
							{
								if(reminder != null)
								{
									eventReminder = <reminder/>;
									eventReminder.setNamespace(gdNs);
									if(reminder.absoluteTime != null)
									{
										eventReminder.@absoluteTime=reminder.absoluteTime;
									}
									
									if(reminder.days != null)
									{
										eventReminder.@days=reminder.days;
									}
									 
									if(reminder.hours != null)
									{
										eventReminder.@hours=reminder.hours;
									}
									
									if(reminder.method != null)
									{
										eventReminder.@method=reminder.method;
									}
									
									if(reminder.minutes != null)
									{
										eventReminder.@minutes=reminder.minutes;
									}										
									
									whenXML.appendChild(eventReminder);
								}
							}																		
						}
						whenXMLList.addItem(whenXML);
					}					
				}
			}
			return whenXMLList;
		}
		
		private function createContentXML(content:ContentVO):XML
		{
			var contentXML:XML = null;
			if(content != null)
			{
				contentXML = <content
				type={content.type}>
				{content.content}
				</content>;				
			}
			return contentXML;			
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
		
		private function createCategoriesXMLCollection(categories:ArrayCollection):XMLListCollection
		{
			var catXMLList:XMLListCollection = null;
			if(categories != null)
			{
				var cat:XML;
				catXMLList = new XMLListCollection();
				for each(var category:CategoryVO in categories)
				{
					if(category != null)
					{
						cat = <category 
								scheme={category.scheme} 
								label={category.label}
								term={category.term}/>;
						catXMLList.addItem(cat);
					}
				}
			}
			return catXMLList;
		}
		
		private function createWhoXMLCollection(whoCollection:ArrayCollection):XMLListCollection
		{
			var whoXMLList:XMLListCollection = null;
			
			if(whoCollection != null)
			{
				whoXMLList = new XMLListCollection();
				var whoXML:XML;
				
				for each(var who:WhoVO in whoCollection)
				{
					if(who != null)
					{
						whoXML = <who email={who.email} rel={who.rel} 
						valueString={who.valueString} attendeeStatus={who.attendeeStatus}
						attendeeType={who.attendeeType}/>;
						
						whoXMLList.addItem(whoXML);
					}
				}				
			}
			return whoXMLList;
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
////////////////////////////functions to create objects from XML response///////////////////////

		private function createCommentsObject(commentsObj:Object):CommentsVO
		{
			var comments:CommentsVO = null;
			
			if(commentsObj != null)
			{
				comments = new CommentsVO();
				comments.rel = commentsObj.rel;
				comments.feedLink = createFeedLinkObject(commentsObj.feedLink);
			}
			return comments;
		}
		
		private function createFeedLinkObject(feedLinkObj:Object):FeedLinkVO
		{
			var feedLink:FeedLinkVO = null;
			if(feedLinkObj != null)
			{
				feedLink = new FeedLinkVO();
				feedLink.countHint = feedLinkObj.countHint;
				feedLink.href = feedLinkObj.href;
				feedLink.readOnly = feedLinkObj.readOnly;
				feedLink.rel = feedLinkObj.rel;
				feedLink.feed = feedLinkObj.feed;
			}
			return feedLink;
		}
		
		private function createWhoCollection(whoObj:Object):ArrayCollection
		{
			var whoCollection:ArrayCollection = null;
			var who:WhoVO = null;
			
			if(whoObj != null && whoObj is ArrayCollection)
			{
				whoCollection = new ArrayCollection();
				var whoColl:ArrayCollection = whoObj as ArrayCollection;
				
				for each(var wh:Object in whoColl)
				{
					if(wh != null)
					{
						who = new WhoVO();
						who.attendeeStatus = wh.attendeeStatus;
						who.attendeeType = wh.attendeeType;
						who.email = wh.email;
						who.rel = wh.rel;
						who.valueString = wh.valueString;
						who.entryLink = createEntryLinkObject(wh.entryLink);
						
						whoCollection.addItem(who);
					}
				}
			}
			else if(whoObj != null)
			{
				whoCollection = new ArrayCollection();
				
				who = new WhoVO();
				who.attendeeStatus = whoObj.attendeeStatus;
				who.attendeeType = whoObj.attendeeType;
				who.email = whoObj.email;
				who.rel = whoObj.rel;
				who.valueString = whoObj.valueString;
				who.entryLink = createEntryLinkObject(whoObj.entryLink);
				
				whoCollection.addItem(who);				
			}
			return whoCollection;
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
		
		private function createWhenCollection(whenCollObj:Object):ArrayCollection
		{
			var whenCollection:ArrayCollection = null;
			var when:WhenVO = null;
			
			if(whenCollObj != null && whenCollObj is ArrayCollection)
			{
				whenCollection = new ArrayCollection();
				var whenColl:ArrayCollection = whenCollObj as ArrayCollection;
				
				for each(var whenObj:Object in whenColl)
				{
					if(whenObj != null)
					{
						when = new WhenVO();
						when.endTime = whenObj.endTime;
						when.startTime = whenObj.startTime;
						when.valueString = whenObj.valueString;
						when.reminders = createReminderCollection(whenObj.reminder);
						
						whenCollection.addItem(when);
					}
				}
			}
			else if(whenCollObj != null)
			{
				whenCollection = new ArrayCollection();
				
				when = new WhenVO();
				when.endTime = whenCollObj.endTime;
				when.startTime = whenCollObj.startTime;
				when.valueString = whenCollObj.valueString;
				when.reminders = createReminderCollection(whenCollObj.reminder);
				
				whenCollection.addItem(when);				
			}
			return whenCollection;
		}
		
		private function createReminderCollection(reminderObj:Object):ArrayCollection
		{
			var reminders:ArrayCollection = null;
			var reminder:ReminderVO = null;
			
			try
			{
				if(reminderObj != null && reminderObj is ArrayCollection)
				{
					reminders = new ArrayCollection();
					var remColl:ArrayCollection = reminderObj as ArrayCollection;
					
					for each(var rem:Object in remColl)
					{
						if(rem != null)
						{
							reminder = new ReminderVO();
							reminder.absoluteTime = rem.absoluteTime;
							if(rem.days != null)
							{
								reminder.days = rem.days;
							}
							
							if(rem.hours != null)
							{
								reminder.hours = rem.hours;
							}
							
							if(rem.minutes != null)
							{
								reminder.minutes = rem.minutes;
							}
							reminder.method = rem.method;
													
							reminders.addItem(reminder);
						}
					}
				}
				else if(reminderObj != null)
				{
					reminders = new ArrayCollection();
					reminder = new ReminderVO();
					reminder.absoluteTime = reminderObj.absoluteTime;
					if(rem.days != null)
					{
						reminder.days = reminderObj.days;
					}
					
					if(rem.hours != null)
					{
						reminder.hours = reminderObj.hours;
					}
					
					if(rem.minutes != null)
					{
						reminder.minutes = reminderObj.minutes;
					}
	
					reminder.method = reminderObj.method;
					
					reminders.addItem(reminder);				
				}				
			}catch(e:Error)
			{
				logMessage("createReminderCollection " + e.message, LOG_LEVEL_ERROR);
			}
			return reminders;
		}
		
		private function createContentObject(contentObj:Object):ContentVO
		{
			var content:ContentVO = null;
			if(contentObj != null && contentObj is String)
			{
				content = new ContentVO();
				content.content = String(contentObj);
				content.type = GoogleCalendarEventUtil.TEXT_CONTENT_TYPE;				
			}
			else if(contentObj != null)
			{
				content = new ContentVO();
				content.content = contentObj.content;
				content.type = contentObj.type;
			}
			return content;
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
		
		private function createCategoryCollection(categoryObj:Object):ArrayCollection
		{
			var categories:ArrayCollection = null;
			var category:CategoryVO = null;
			if(categoryObj != null && categoryObj is ArrayCollection)
			{
				categories = new ArrayCollection();
				var catColl:ArrayCollection = categoryObj as ArrayCollection;
				for each (var cat:Object in catColl)
				{
					if(cat != null)
					{
						category = new CategoryVO();
						category.scheme = cat.scheme;
						category.term = cat.term;
						category.label = cat.label;
						categories.addItem(category);						
					}
				}
			}
			else if(categoryObj != null)
			{
				categories = new ArrayCollection();
				category = new CategoryVO();
				category.scheme = categoryObj.scheme;
				category.term = categoryObj.term;
				category.label = categoryObj.label;
				categories.addItem(category);				
			}
			return categories;
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
	}
}