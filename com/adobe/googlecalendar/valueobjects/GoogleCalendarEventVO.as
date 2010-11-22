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
	 * This VO represents a Event in a calendar
	 * Schema details can be found at this URL
	 * http://code.google.com/apis/gdata/elements.html#gdEventKind
	 */ 

	import com.adobe.googlecalendar.errors.InvalidArgumentsError;
	import com.adobe.googlecalendar.services.GoogleCalendarEventUtil;
	
	import mx.collections.ArrayCollection;

	public class GoogleCalendarEventVO
	{
		public var id:String;
		public var etag:String;
		public var published:String;
		public var updated:String;
		
		//schema allows for more than one category element
		//making it a collection to handle that
		//will contain CategoryVO objects
		public var category:ArrayCollection;		
		public var title:TitleVO;
		
		public var comments:CommentsVO;
		
		public var content:ContentVO;
		public var links:ArrayCollection;
		public var author:AuthorVO;
		
		public var recurrence:String;
		public var eventStatus:String;
		public var visibility:String;
		public var transparency:String;
		public var uid:String;
		public var sequence:String;
		
		//schema allows multiple when tags
		//making it a collection to support that
		//will contain WhenVO objects
		public var when:ArrayCollection;
		
		//contains WhoVO objects
		public var who:ArrayCollection;
		
		//contains WhereVO objects
		public var where:ArrayCollection;
		
		//this is not used currently. we might be using this to
		//store the actual XML returned from the server
		public var eventXML:XML;
		
		public function setTitle(
		text:String, 
		type:String=GoogleCalendarEventUtil.TEXT_CONTENT_TYPE):void
		{
			if(this.title == null)
			{
				this.title = new TitleVO();
			}

			this.title.title = text;
			this.title.type = type;
		}
		
		public function setContent(
		text:String, 
		type:String=GoogleCalendarEventUtil.TEXT_CONTENT_TYPE):void
		{
			if(this.content == null)
			{
				this.content = new ContentVO();
			}

			this.content.content = text;
			this.content.type = type;
		}
		
		public function addWhere(
		valueString:String,
		rel:String=null,
		label:String="Event location"):WhereVO
		{
			var eventWhere:WhereVO = new WhereVO();
			if(rel == null)
			{
				rel = GoogleCalendarEventUtil.WHERE_DEFAULT_REL;
			}
			eventWhere.valueString = valueString;
			eventWhere.rel = rel;
			eventWhere.label = label;
			if(this.where == null)
			{
				where = new ArrayCollection();
			}
			if(eventWhere != null)
			{
				where.addItem(eventWhere);
			}
			
			return eventWhere;
		}
		
		public function addWhen(
		startDateTime:Date, 
		endDateTime:Date, 
		isFullDay:Boolean):WhenVO
		{
			var eventWhen:WhenVO = new WhenVO();
			startDateTime = startDateTime != null ? startDateTime : new Date();
			endDateTime = endDateTime != null ? endDateTime : startDateTime;
			
			if(endDateTime.getTime() < startDateTime.getTime())
			{
				throw new InvalidArgumentsError("End date cannot be before start date");
			}
			
			var startDateTimeStr:String = startDateTime.fullYearUTC + "-" + 
			((startDateTime.monthUTC + 1) < 10 ? "0" + (startDateTime.monthUTC + 1):(startDateTime.monthUTC + 1)) + "-" + 
			(startDateTime.dateUTC < 10 ? "0" + startDateTime.dateUTC:startDateTime.dateUTC);
			
			var endDateTimeStr:String = endDateTime.fullYearUTC + "-" + 
			((endDateTime.monthUTC + 1) < 10 ? "0" + (endDateTime.monthUTC + 1):(endDateTime.monthUTC + 1)) + "-" + 
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
			
			eventWhen.startTime = startDateTimeStr;
			eventWhen.endTime = endDateTimeStr;
			
			if(this.when == null)
			{
				this.when = new ArrayCollection();				
			}
			
			if(eventWhen != null)
			{
				this.when.addItem(eventWhen);
			}
			return eventWhen;
		}
		
		public function isFullDay():Boolean
		{ 
			var result:Boolean = false;
			var whenobj:WhenVO;
			
			if(this.when != null && this.when.length > 0)
			{
				whenobj = when.getItemAt(0) as WhenVO;
				if(whenobj != null)
				{
					if(whenobj.startTime != null && 
					whenobj.startTime.indexOf("T") == -1)
					{
						result = true;
					}
				}
			}
			return result;
		}
		
		public function GoogleCalendarEventVO()
		{
		}

	}
}