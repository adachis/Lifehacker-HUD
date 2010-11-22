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
	 * This VO represents a Google Calendar element
	 */
	import com.adobe.googlecalendar.services.GoogleCalendarEventUtil;
	
	import mx.collections.ArrayCollection;
	[Bindable]
	public class GoogleCalendarVO
	{
		public static const calendarColors:Array = ["#A32929","#B1365F","#7A367A","#5229A3",
													"#29527A","#2952A3","#1B887A",
											  		"#28754E","#0D7813","#528800","#88880E",
											  		"#AB8B00","#BE6D00","#B1440E",
													"#865A5A","#705770","#4E5D6C",
													"#5A6986","#4A716C","#6E6E41","#8D6F47"];
		
		public var id:String;
		public var published:String;
		public var updated:String;
		
		public var title:TitleVO;
		
		
		public var summary:SummaryVO;
		
		//will contain LinkVO objects
		public var links:ArrayCollection;
		
		public var author:AuthorVO;
		
		public var timeZone:String;
		public var hidden:Boolean;
		public var color:String;
		public var selected:Boolean;
		public var accessLevel:String;
		
		//schema allows multiple entries
		//will contain WhereVO objects
		public var where:ArrayCollection;
		
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
		
		public function setSummary(
		text:String,
		type:String=GoogleCalendarEventUtil.TEXT_CONTENT_TYPE):void
		{
			if(this.summary == null)
			{
				this.summary = new SummaryVO();
			}
			this.summary.summary = text;
			this.summary.type = type;
		}
		
		public function addWhere(
		valueString:String,
		rel:String="",
		label:String="Calendar location"):WhereVO
		{
			var calWhere:WhereVO = new WhereVO();

			calWhere.valueString = valueString;
			calWhere.rel = rel;
			calWhere.label = label;
			if(this.where == null)
			{
				where = new ArrayCollection();
			}
			where.addItem(calWhere);
			return calWhere;
		}

		public function GoogleCalendarVO()
		{
		}
	}
}