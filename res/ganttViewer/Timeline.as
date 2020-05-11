package ganttViewer{
	import flash.display.*;
	import flash.text.*;
	import flash.events.*;
	import flash.geom.*;
	import fl.transitions.*;
	import fl.transitions.easing.*;

	import com.ericfeminella.collections.HashMap; 
	
	public class Timeline extends Sprite{
		
		private var _graphStage:GanttStage;
		private var _graphicWidth:Number;
		private var _globalStartDate:Date;
		private var _globalEndDate:Date;
		private var _leftMargin:uint;
		private var _dayWidth:Number;
		private var _yearTextFormat:TextFormat;
		private var _monthTextFormat:TextFormat;
		private var _locallang:Array;
		private var _langKey:String;
		
		var tlBackground:Shape;
		var ruler:Shape;
		var scaleFactor:Number=1;
		
		public function Timeline(graphStage:GanttStage, graphicWidth:Number, globalStartDate:Date, globalEndDate:Date, leftMargin:uint, dayWidth:Number, locallang:Array, langKey:String){
			_graphStage = graphStage;
			_graphicWidth=graphicWidth;
			_globalStartDate=globalStartDate;
			_globalEndDate=globalEndDate;
			_leftMargin=leftMargin;
			_dayWidth=dayWidth;
			_locallang=locallang;
			_langKey=langKey;
			
			//height=100;
			
			//_graphStage.addEventListener(MouseEvent.MOUSE_MOVE, update_x);
			_graphStage.addEventListener(Event.ENTER_FRAME, update_y);
			_graphStage.addEventListener(Event.ENTER_FRAME, update_y);
			
			
			_yearTextFormat = new TextFormat();
			_yearTextFormat.font = "Verdana";
			_yearTextFormat.size = 10;
			_yearTextFormat.bold = true;
			_yearTextFormat.align = "left";
			
			
			_monthTextFormat = _yearTextFormat
			_monthTextFormat.size = 9;
			_monthTextFormat.bold = false;
			
			drawTimeline();
				
		}
		
		private function drawTimeline(){
			
			//drawing background
			tlBackground = new Shape();
			tlBackground.graphics.beginFill(0xf0f0f0);
			tlBackground.graphics.drawRect(-100,0, _graphicWidth*4, 30);
			
			ruler = new Shape();
			
			//add a month to end date
			if(_globalEndDate.month <11){
				_globalEndDate.setMonth(_globalEndDate.month+1);
			}
			else{
				_globalEndDate.setMonth(0);
				_globalEndDate.setFullYear(_globalEndDate.fullYear);
			}
			
			//stageBackground.graphics.drawRect(-visualAreaWidth,-visualAreaHeight, visualAreaWidth*3+graphicWidth, visualAreaHeight*3+graphicHeight);
			tlBackground.graphics.endFill();
			addChild(tlBackground);
			
			var isFirstYearInGraph:Boolean=true;
			var isFirstMonthInGraph:Boolean=true;
			var isFirstDayInGraph:Boolean=true;
			
			var numYearsSinceDrawingBeginning:uint=0;
			var numMonthsSinceDrawingBeginning:uint=0;
			var numDaysSinceDrawingBeginning:uint=0;
			
			var monthMarker:uint=0;
			//drawing ruler
			for(var year:uint=_globalStartDate.fullYear; year<_globalEndDate.fullYear+1; year++){
				
				//drawing years labels
				var yearLabel:TextField = new TextField();
				yearLabel.text = year.toString();
				yearLabel.y=2;
				yearLabel.width=100;
				yearLabel.height=15;
				yearLabel.setTextFormat(_yearTextFormat);
				yearLabel.selectable=false;
				
				trace("_globalStartDate.month="+_globalStartDate.month);
				
				var month:uint;
				var maxMonth:uint=12;
				
				if(isFirstYearInGraph){
					//write first year label immediatly
					yearLabel.x=_leftMargin;
					isFirstYearInGraph=false;
					month = _globalStartDate.month;
				}
				else{
					//else count distance in days from the beginning
					yearLabel.x=_leftMargin+numDaysSinceDrawingBeginning*_dayWidth;
					ruler.graphics.lineStyle(2,0,1);
					ruler.graphics.moveTo(yearLabel.x, 30);
					ruler.graphics.lineTo(yearLabel.x,10);
					month = 0;
				}
				if(year==_globalEndDate.fullYear)maxMonth=_globalEndDate.month+1;
				   
				addChild(yearLabel);
				
				//drawing month labels
				for(; month<maxMonth; month++){
					//trace("Month="+(month+1));
					ruler.graphics.lineStyle(2,0,1);
					
					//drawing month labels
					var monthLabel:TextField = new TextField();
					monthLabel.text = _locallang[_langKey]["month_"+(month+1)];
					monthLabel.y=12;
					monthLabel.width=100;
					monthLabel.height=15;
					monthLabel.setTextFormat(_monthTextFormat);
					monthLabel.selectable=false;
				
					var day:uint;
					var maxDay:uint=get_days_in_month(year, month+1);
					
					if(isFirstMonthInGraph){
						//write first month label immediatly
						monthLabel.x=_leftMargin;
						isFirstMonthInGraph=false;
						day = _globalStartDate.getDate();
					}
					else{
						//else count distance in days from the beginning
						monthLabel.x=_leftMargin+numDaysSinceDrawingBeginning*_dayWidth;
						ruler.graphics.lineStyle(1,0,1);
						ruler.graphics.moveTo(monthLabel.x, 30);
						ruler.graphics.lineTo(monthLabel.x,20);
						day = 1;
					}
					if(month==_globalEndDate.month)maxDay=_globalEndDate.getDate();
					addChild(monthLabel);
					
					//trace("Month="+(month+1)+" maxDay="+maxDay);
					
					//drawing days
					for(; day<maxDay; day++){
						//trace("Day="+day);
						ruler.graphics.lineStyle(1,0,1);
						
												
						if((day%5==0) && (day < 30) ){
							//else count distance in days from the beginning
							var day_x:uint =_leftMargin+numDaysSinceDrawingBeginning*_dayWidth;
							ruler.graphics.lineStyle(1,0,1);
							ruler.graphics.moveTo(day_x, 30);
							ruler.graphics.lineTo(day_x, 26);
						}
						
						numDaysSinceDrawingBeginning++;
					}
					
					numMonthsSinceDrawingBeginning++;
				}
				
				//textField displaying the title of the projext
				numYearsSinceDrawingBeginning++;
			
			}
			
			addChild(ruler);
			
			
		}
		
		private function update_x(event:Event):void {
			x = _graphStage.x;			
			//trace("x="+x);
		}
		
		private function update_y(event:Event):void {
			y=0;			
		}
		
		public function zoomIn():void {
			scaleFactor = scaleFactor + 5/20
			if(scaleFactor <1)scaleFactor=1;
			//scaleX = scaleFactor;
			new fl.transitions.Tween(this , "scaleX" , Back.easeOut ,this.scaleX , scaleFactor , 6 , false);
		}
		
		public function zoomOut():void {
			scaleFactor = scaleFactor + -5/20
			if(scaleFactor <1)scaleFactor=1;
			//scaleX = scaleFactor;
			new fl.transitions.Tween(this , "scaleX" , Back.easeOut ,this.scaleX , scaleFactor , 6 , false);
		}
		
		public function moveLeft():void {
			//x-=40;
			new fl.transitions.Tween(this , "x" , Back.easeOut ,this.x , this.x-60 , 6 , false);
		}
		
		public function moveRight():void {
			//x+=40;
			new fl.transitions.Tween(this , "x" , Back.easeOut ,this.x , this.x+60 , 6 , false);
		}
		
		
		private function get_days_in_month(yyyy:uint, mm:uint):* {
		   if(yyyy < 1800)
			   return false;
		   if( (mm < 1) || (mm > 12))
			   return false;
		   return mm == 2 ? (yyyy % 4 ? 28 : (yyyy % 100 ? 29 : (yyyy % 400 ? 28 : 29))) : ((mm - 1) % 7 % 2 ? 30 : 31);
       }
	   
       private function get_days_in_year(yyyy:uint) {
            if(yyyy < 1800)
                return false;
       		return(337 + get_days_in_month(yyyy, 2));
       }
		
	}
	
}