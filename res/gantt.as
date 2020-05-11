package {
    //import flash.display.MovieClip;
	import ganttViewer.*;

	import flash.display.*;
	import flash.events.*;
	import flash.net.*;
	import flash.utils.*;
	import flash.text.*;
	
	//import de.polygonal.ds.*; 
	import com.ericfeminella.collections.HashMap;
   
    public class gantt extends MovieClip {
		
		var pluginPageUID:String; //id of the page containing our plugin
		var extPath:String;	//path of the extension
		var langKey:String; //ISO2 code for extension current language (eg: fr, us, es...)
		var selectedPids:String; //Pids where to find records
		var prefixId:String; //Prefix for typo3 extension
		
		
		var globalStartDate:Date; //start date of the timeline
		var globalEndDate:Date; //end date of the timeline
		var globalDaysCount:String;	//length in days of the timeline
		
		var graphStage:GanttStage;
		var border:Shape;
		
		var dayWidth:Number=2; //basic width of a day in pixels
		var projectHeight:Number=40; //basic height of a project in pixels
		var leftMargin:uint = 10; 
		var topMargin:uint = 40; 
		
		var requestProjectURL:String;
		var requestLocallangURL:String;
			
		var projectXML:XML; //XML File describing projects
		var locallangXML:XML; //XML File with localized text
		
		var locallang:Array = new Array(); //localisation
		var projects:Array = new Array(); //list of project data
		
		var barHeight:uint = 8;
		
		var timeline:Timeline;
		
		var MODE = "PROD"; //"PROD" or "DEBUG". DEBUG enables local file loading
		
		//var stageScale:Number=1;
			
        public function gantt() {
			
            //loading flashVars
			pluginPageUID = root.loaderInfo.parameters.pluginPageUID;
			var xmlType:String = root.loaderInfo.parameters.xmlType;
			extPath = root.loaderInfo.parameters.extPath;
			langKey = root.loaderInfo.parameters.langKey;
			selectedPids = root.loaderInfo.parameters.selectedPids;
			prefixId = root.loaderInfo.parameters.prefixId;
			
			if(MODE=="PROD"){
				requestProjectURL = "index.php?id="+pluginPageUID+"&type="+xmlType+"&selectedPids="+selectedPids+"&prefixId="+prefixId;
				requestLocallangURL = extPath+"pi1/locallang.xml";
			}
			else{
				requestProjectURL = "test.txt";
				requestLocallangURL = "../pi1/locallang.xml";
				langKey = "fr";
			}
			
			//getting controls toolbar
			controls = controlBar(getChildByName("controls"));
			controls.x=1;
			controls.y=stage.stageHeight-21;
			controls.width=stage.stageWidth-1;
			
			controls.zoomPlus = ZoomPlus(controls.getChildByName("zoomPlus"));
			controls.zoomPlus.addEventListener(MouseEvent.MOUSE_DOWN, zoomIn);
			
			controls.zoomMinus = ZoomMinus(controls.getChildByName("zoomMinus"));
			controls.zoomMinus.addEventListener(MouseEvent.MOUSE_DOWN, zoomOut);
			
			controls.arrowControls = arrows(controls.getChildByName("arrowControls"));
			
			controls.arrowControls.leftArrow = arrow_left(controls.arrowControls.getChildByName("leftArrow"));
			controls.arrowControls.leftArrow.addEventListener(MouseEvent.MOUSE_DOWN, moveLeft);
			
			controls.arrowControls.rightArrow = arrow_right(controls.arrowControls.getChildByName("rightArrow"));
			controls.arrowControls.rightArrow.addEventListener(MouseEvent.MOUSE_DOWN, moveRight);
			
			controls.arrowControls.upArrow = arrow_up(controls.arrowControls.getChildByName("upArrow"));
			controls.arrowControls.upArrow.addEventListener(MouseEvent.MOUSE_DOWN, moveUp);
			
			controls.arrowControls.downArrow = arrow_down(controls.arrowControls.getChildByName("downArrow"));
			controls.arrowControls.downArrow.addEventListener(MouseEvent.MOUSE_DOWN, moveDown);
			
			
			trace("loading project data from file : "+requestProjectURL);
			trace("loading localization data from file : "+requestLocallangURL);
			trace("extension path : "+extPath);
			trace("extension language : "+langKey);
			
			
			//loading Project XML
			var loader:URLLoader = new URLLoader();
			loader.dataFormat=URLLoaderDataFormat.TEXT;
			loader.addEventListener(Event.COMPLETE, processXML);
			loader.load(new URLRequest(requestProjectURL));
			
			//TransitionManager.start(img1_mc, {type:Zoom, direction:Transition.IN, duration:2, easing:Elastic.easeOut});
			trace("stage.stageWidth="+stage.stageWidth);
			trace("stage.stageHeight="+stage.stageHeight);
			
			//draw border
			border = new Shape();
			border.graphics.lineStyle(1,0xf0f0f0,100);
			border.graphics.drawRect(0,0, stage.stageWidth-1, stage.stageHeight-1);
			border.graphics.endFill();
			
			
			
        }
		
		
		
		function processXML(event:Event):void{
			try{
				trace("");
				trace("Processing Projects XML...");
				
				//convert the downloaded text into an XML instance
				var projectXML:XML = new XML(event.target.data);
				
				//parsing Global data
				/*globalStartDate = new Date(Number(parseInt(projectXML.global.rangeStartDate.@timestamp)));
				globalEndDate = new Date(Number(parseInt(projectXML.global.rangeEndDate.@timestamp)));
				trace("globalStartDate="+globalStartDate+" timestamp.orig="+projectXML.global.rangeStartDate.@timestamp+" timestamp.this="+globalStartDate.valueOf() );
				trace("globalEndDate="+globalEndDate);
				trace("test="+(new Date(1196463600)));*/
				
				globalStartDate = new Date();
				globalStartDate.setDate(projectXML.global.rangeStartDate.@day);
				globalStartDate.setMonth(projectXML.global.rangeStartDate.@month-1);
				globalStartDate.setFullYear(projectXML.global.rangeStartDate.@year);
				
				globalEndDate = new Date();
				globalEndDate.setDate(projectXML.global.rangeEndDate.@day);
				globalEndDate.setMonth(projectXML.global.rangeEndDate.@month-1);
				globalEndDate.setFullYear(projectXML.global.rangeEndDate.@year);
				
				trace("globalStartDate="+globalStartDate);
				trace("globalEndDate="+globalEndDate);
				
				globalDaysCount = projectXML.global.rangeLength.@daysCount;
				
				
				trace("globalDaysCount="+globalDaysCount);
				
				trace("Processing projects XML");
				processProjects(projectXML);
				trace("Processed projects XML");
				
				//loading locallang XML after we are sure projects have been processed
				var loaderLang:URLLoader = new URLLoader();
				loaderLang.dataFormat=URLLoaderDataFormat.TEXT;
				loaderLang.addEventListener(Event.COMPLETE, processLocallangXML);
				loaderLang.load(new URLRequest(requestLocallangURL));
				
				
			}
			catch(e:TypeError){
				trace("Could not parse text into XML (Projects)");
				trace(e.message);
			}
			
			graphStage = new GanttStage(stage.stageWidth, stage.stageHeight, dayWidth, projectHeight,  parseInt(globalDaysCount), projects.length, leftMargin, topMargin);
		
			
			trace("Adding projects to stage");
			for (var i:uint=0;i<projects.length;i++){
				projects[i].drawBar(i,leftMargin,topMargin,barHeight,dayWidth,projectHeight);
				graphStage.addChild(projects[i]);
				trace("added project "+projects[i].title+" to stage");
				trace("");trace("");trace("");
			}
			trace("Done.");
			
			
			
			/*
			var bar:GanttBar = new GanttBar();
			graphStage.addChild(bar);*/
			
			//addEventListener(MouseEvent.MOUSE_WHEEL,scaleStage);
			
			addChild(graphStage);
			
			
			//make sure control bar is on top
			var higherIndex:uint = numChildren-1;
			setChildIndex(controls,higherIndex);
			
			//then add border over
			addChild(border);
		}
		
		//processing localized text XML
		function processLocallangXML(event:Event):void{
			try{
				trace("");
				trace("Processing locallang XML...");
				
				//convert the downloaded text into an XML instance
				locallangXML = new XML(event.target.data);
				
				
				//parsing language keys
				for each(var langTag:XML in locallangXML.data..languageKey){
					//trace("Found language="+langTag.@index);
					langKey=langTag.@index;
					locallang[langKey]=new Array();
					
					//parsing keys
					for each(var label:XML in langTag.elements()){
						//trace("Found key="+label.@index+" value="+label.toString());
						locallang[langKey][label.@index]=label.toString();
					}
				}
				trace("");
				trace("Done Processing locallang XML.");
				
				timeline=new Timeline(graphStage,leftMargin+parseInt(globalDaysCount)*dayWidth+ stage.stageWidth, globalStartDate, globalEndDate, leftMargin, dayWidth , locallang, langKey);
				
				controls.zoomText = TextField(controls.getChildByName("zoomText"));
				controls.zoomText.text = locallang[langKey]["zoom"];
				
				//controls.dragText = TextField(controls.getChildByName("dragText"));
				//controls.dragText.text = locallang[langKey]["drag"];
				
				//ad controls and timeline once the localized text has been set
				addChild(timeline);
				setChildIndex(timeline,numChildren-1); //timeline goes on top
				//setChildIndex(graphStage,numChildren-1); //timeline goes on top
				setChildIndex(controls,numChildren-1); //controls goes on top
				
			}
			catch(e:TypeError){
				trace("Could not parse text into XML (Locallang)");
				trace(e.message);
			}
						
			
		}
		
		
		//processing project data
		function processProjects(projectXML:XML):void{
			//parsing Projects
			
			for each(var project:XML in projectXML.project){
				
				var currentProject:GanttProject=new GanttProject();
				currentProject.link=project.link;
				currentProject.uid=project.uid;
				currentProject.pid=project.pid;
				currentProject.title=project.title;
				
				trace("");
				trace("*****READING PROJECT XML FOR "+currentProject.title+" *****");
				
				//estimated data
				currentProject.computedEstimatedStartDate = new Date();
				currentProject.computedEstimatedStartDate.setDate(project.computedEstimatedStartDate.@day);
				currentProject.computedEstimatedStartDate.setMonth(project.computedEstimatedStartDate.@month);
				currentProject.computedEstimatedStartDate.setFullYear(project.computedEstimatedStartDate.@year);
				
				currentProject.computedEstimatedEndDate = new Date();
				currentProject.computedEstimatedEndDate.setDate(project.computedEstimatedEndDate.@day);
				currentProject.computedEstimatedEndDate.setMonth(project.computedEstimatedEndDate.@month);
				currentProject.computedEstimatedEndDate.setFullYear(project.computedEstimatedEndDate.@year);
				
				currentProject.estimatedMapFusioned = processMap(project.estimatedMapFusioned as XMLList);
						
				//real world data
				currentProject.computedRealStartDate = new Date();
				currentProject.computedRealStartDate.setDate(project.computedRealStartDate.@day);
				currentProject.computedRealStartDate.setMonth(project.computedRealStartDate.@month);
				currentProject.computedRealStartDate.setFullYear(project.computedRealStartDate.@year);
				
				currentProject.computedRealEndDate = new Date();
				currentProject.computedRealEndDate.setDate(project.computedRealEndDate.@day);
				currentProject.computedRealEndDate.setMonth(project.computedRealEndDate.@month);
				currentProject.computedRealEndDate.setFullYear(project.computedRealEndDate.@year);
				
				currentProject.realMapFusioned = processMap(project.realMapFusioned as XMLList);
				
				projects.push(currentProject);
				
				trace("*****DONE READING PROJECT XML*****");
			
				//processing child projects
				processProjects(project);
				
				
				
			}
		}
		
			
		
		function processMap(xmlData:XMLList):HashMap{
			var map:HashMap = new HashMap();
			
			for each(var yearNode:XML in xmlData.year){
				//trace("year="+parseInt(yearNode.@num));
				var monthMap:HashMap = new HashMap();
					
				for each(var monthNode:XML in yearNode.month){
					//trace("month="+parseInt(monthNode.@num));
					//inserting the count of days in the month first
					var numDays:uint = parseInt(monthNode.@numDays);
					monthMap.put("numDays",numDays);
					
					var dayContainer:Object = new Object();
					dayContainer.dayMap = new HashMap();
					dayContainer.dayNumMap = new HashMap();
					
					for each(var dayNode:XML in monthNode.day){
						//trace("day="+parseInt(dayNode.@num)+" value="+parseInt(dayNode));
						//trace("day="+parseInt(dayNode.@num)+" absNum="+parseInt(dayNode.@absNum));
						dayContainer.dayMap.put(parseInt(dayNode.@num), parseInt(dayNode));
						dayContainer.dayNumMap.put(parseInt(dayNode.@num), parseInt(dayNode.@absNum));
					}
					monthMap.put(parseInt(monthNode.@num), dayContainer);
				}
				map.put(parseInt(yearNode.@num), monthMap);
			}
			return map;
		}
		
		
		private function zoomIn(event:MouseEvent):void {
			graphStage.zoomIn();
			timeline.zoomIn();
		}
		
		private function zoomOut(event:MouseEvent):void {
			graphStage.zoomOut();
			timeline.zoomOut();
		}
		
		private function moveLeft(event:MouseEvent):void {
			if(graphStage.x<0){
				graphStage.moveRight();
				timeline.moveRight();
			}
			
		}
		
		private function moveRight(event:MouseEvent):void {
			graphStage.moveLeft();
			timeline.moveLeft();
		}
		
		private function moveUp(event:MouseEvent):void {
			if(graphStage.y<0){
				graphStage.moveDown();
			}
		}
		
		private function moveDown(event:MouseEvent):void {
			graphStage.moveUp();
		}
    }
}