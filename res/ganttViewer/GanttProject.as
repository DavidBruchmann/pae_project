package ganttViewer{
	import flash.display.*;
	import flash.geom.Matrix;
	import flash.text.*;
	
	import com.ericfeminella.collections.HashMap; 
	
	public class GanttProject extends Sprite{
		
		private var _projectHeight; //height of a project graph
		private var _topMargin; //decay so there is room for the timeline
		
		public var link:String;
		public var uid:uint;
		public var pid:uint;
		public var title:String;
		
		//estimated data
		public var computedEstimatedStartDate:Date;
		public var computedEstimatedEndDate:Date;
		public var estimatedMapFusioned:HashMap;
		
		//real world data
		public var computedRealStartDate:Date;
		public var computedRealEndDate:Date;
		public var realMapFusioned:HashMap;
		
		private var _format:TextFormat;
		
		public function GanttProject(){
			

			
		}
		
		public function createCircle(color:uint, radius:Number):Shape{
			var shape:Shape = new Shape();
			shape.graphics.beginFill(color);
			shape.graphics.drawCircle(0,0, radius);
			shape.graphics.endFill();
			return shape;
		}
		
		public function drawBar(position:uint, leftMargin:uint,topMargin:Number, barHeight:uint, dayWidth:Number, projectHeight:Number){
			
			var type:String = GradientType.LINEAR;
			var colorsEstimated:Array = [[0xe4e4e4, 0xa6a6a6],[0x7d7d7d, 0x353535]];
			var colorsReal:Array = [[0xffddac, 0xffa777],[0xff9800, 0xff5a00]];
			var alphas:Array = [1, 1];
			var ratios:Array = [0, 255];
			var spreadMethod:String = SpreadMethod.PAD;
			var interp:String = InterpolationMethod.LINEAR_RGB;
			var focalPtRatio:Number = 0;
			
			_projectHeight = projectHeight;
			_topMargin = topMargin;
			
			var boxWidth:Number = dayWidth;
			var boxHeight:Number = barHeight;
			var boxRotation:Number = Math.PI/2; // 90˚
			
						
			//record the postion of years since the begining of the timeline
			var yearPositions= new HashMap();
			var yearCounter=0;
			
			//record the postion of monthes since the begining of the timeline
			var monthPositions= new HashMap();
			var monthCounter=0;
			
			//switches between non worked days (0) and worked days (1) colors
			var colorSwitch:uint =0;
			
			//textField displaying the title of the projext
			var prjTitle:TextField = new TextField();
			prjTitle.text = title;
			prjTitle.y=_topMargin+_projectHeight*position;
			prjTitle.width=500;
			prjTitle.selectable=false;
											
			_format = new TextFormat();
			_format.font = "Verdana";
			_format.size = 10;
			_format.bold = true;
			_format.align = "left";
			_format.url = link;
			
			prjTitle.setTextFormat(_format);
			
			var firstDayEstimated_x:uint;
			var firstDayReal_x:uint;
			
			//drawing Estimated data gantt bar
			var yearKeys:Array = estimatedMapFusioned.getKeys().sort(Array.NUMERIC);
			var isFirstDayInMap:Boolean=true;
			
			for each(var yearKey:uint in yearKeys){
				//recording year position
				yearPositions.put(yearKey,yearCounter++);
				//trace ("Drawing year "+yearKey);
				//trace("year="+yearKey+" : "+estimatedMapFusioned.find(yearKey));
				var monthMap:HashMap = estimatedMapFusioned.getValue(yearKey);
				var monthKeys:Array = monthMap.getKeys();
								
				for each(var monthKey:* in monthKeys){
					//trace ("	Drawing month "+monthKey);
					monthPositions.put(monthKey,monthCounter++);
					
					//trace("key="+monthKey+" typeof="+typeof(monthKey));
					//trace("month="+monthKey+" : "+monthMap.getValue(monthKey));
					if(typeof(monthKey)=="number"){
						var dayContainer:Object = monthMap.getValue(monthKey);
						
						var dayKeys:Array = dayContainer.dayMap.getKeys().sort(Array.NUMERIC);
						var dayValues:Array = dayContainer.dayMap.getValues();
						var dayAbsNums:Array = dayContainer.dayNumMap.getValues();
						//trace("dayAbsNums="+dayAbsNums);
						
						var firstDayKey:uint;
						var isFirstDayInMonth:Boolean=true;
						for each(var dayKey:* in dayKeys){
							if(isFirstDayInMonth){
								firstDayKey=dayKey;
								isFirstDayInMonth=false;
							}
							
							
							var posY:uint =_topMargin+_projectHeight*position+_format.size+7;
							//trace ("posY="+posY+" _topMargin="+_topMargin+" _projectHeight="+_projectHeight+" position="+position);
							
							var matrix:Matrix = new Matrix();
							var tx:Number = leftMargin+dayWidth*dayAbsNums[dayKey-firstDayKey];
							var ty:Number = posY;
							
							if(isFirstDayInMap){
								//trace("*******************ESTIMATED FirstDayInMAP !!! y="+yearKey+" m="+monthKey+" d="+dayKey+" tx="+tx);
								firstDayEstimated_x=tx;
								isFirstDayInMap=false;
							}
							
							colorSwitch = (dayContainer.dayMap.getValue(dayKey)>0)?1:0;
							
							//trace("leftMargin="+leftMargin+" dayKey="+dayKey+" dayAbsNums[dayKey]="+dayAbsNums[dayKey-firstDayKey]);
							//trace("tx="+tx+" ty="+ty);
							matrix.createGradientBox(dayWidth, barHeight, boxRotation, tx, ty);
							
							var square:Shape = new Shape;
							square.graphics.beginGradientFill(type, 
														colorsEstimated[colorSwitch],
														alphas,
														ratios, 
														matrix, 
														spreadMethod, 
														interp, 
														focalPtRatio);
							square.graphics.drawRect(tx, ty, dayWidth, barHeight);
							addChild(square);
						}
					}
				}
			}
			
			//drawing Real data gantt bar
			yearKeys = realMapFusioned.getKeys().sort(Array.NUMERIC);
			isFirstDayInMap = true;
			
			for each(yearKey in yearKeys){
				//recording year position
				yearPositions.put(yearKey,yearCounter++);
				//trace ("Drawing year "+yearKey);
				//trace("year="+yearKey+" : "+realMapFusioned.find(yearKey));
				monthMap = realMapFusioned.getValue(yearKey);
				monthKeys = monthMap.getKeys();
								
				for each(monthKey in monthKeys){
					//trace ("	Drawing month "+monthKey);
					monthPositions.put(monthKey,monthCounter++);
					
					//trace("key="+monthKey+" typeof="+typeof(monthKey));
					//trace("month="+monthKey+" : "+monthMap.getValue(monthKey));
					if(typeof(monthKey)=="number"){
						dayContainer = monthMap.getValue(monthKey);
						
						dayKeys = dayContainer.dayMap.getKeys().sort(Array.NUMERIC);
						dayValues = dayContainer.dayMap.getValues();
						dayAbsNums = dayContainer.dayNumMap.getValues();
						//trace("dayAbsNums="+dayAbsNums);
						
						isFirstDayInMonth=true;
						for each(dayKey in dayKeys){
							if(isFirstDayInMonth){
								firstDayKey=dayKey;
								isFirstDayInMonth=false;
							}
							
							//trace ("		Drawing day "+dayKey);
							posY =_topMargin+_projectHeight*position+_format.size+7;
							
							matrix = new Matrix();
							tx = leftMargin+dayWidth*dayAbsNums[dayKey-firstDayKey];
							ty = posY +barHeight+2;
							
							if(isFirstDayInMap){
								//trace("*******************REAL FirstDayInMAP !!! y="+yearKey+" m="+monthKey+" d="+dayKey+" tx="+tx);
								firstDayReal_x=tx;
								isFirstDayInMap=false;
							}
							/*
							trace("y="+yearKey+" m="+monthKey+" d="+dayKey+" tx="+tx);
							if(isNaN(tx)){
								trace("dayKey="+dayKey);
								trace("firstDayKey="+firstDayKey);
								trace("dayAbsNums[dayKey-firstDayKey]="+dayAbsNums[dayKey-firstDayKey]);
							}*/
								
							colorSwitch = (dayContainer.dayMap.getValue(dayKey)>0)?1:0;
							
							//trace("leftMargin="+leftMargin+" dayKey="+dayKey+" dayAbsNums[dayKey]="+dayAbsNums[dayKey-firstDayKey]);
							//trace("tx="+tx+" ty="+ty);
							matrix.createGradientBox(dayWidth, barHeight, boxRotation, tx, ty);
							
							square = new Shape;
							square.graphics.beginGradientFill(type, 
														colorsReal[colorSwitch],
														alphas,
														ratios, 
														matrix, 
														spreadMethod, 
														interp, 
														focalPtRatio);
							square.graphics.drawRect(tx, ty, dayWidth, barHeight);
							addChild(square);
						}
					}
				}
			}
			
			//adding textfield with project title using begining position of leftmost bar in graph
			prjTitle.x=(firstDayEstimated_x < firstDayReal_x)?firstDayEstimated_x:firstDayReal_x;
			addChild(prjTitle);
								
			
		
			

		}
		
		override public function toString():String {
			var output:String;
			
			output = "link : "+link+"\n";
			output += "uid : "+uid+"\n";
			output += "pid : "+pid+"\n";
			output += "title : "+title+"\n";
			
			
			//estimated data
			output += "computedEstimatedStartDate : "+computedEstimatedStartDate+"\n";
			output += "computedEstimatedEndDate : "+computedEstimatedEndDate+"\n";
			//output += "estimatedMapFusioned : "+estimatedMapFusioned+"\n";
						
			//real world data
			output += "computedRealStartDate : "+computedRealStartDate+"\n";
			output += "computedRealEndDate : "+computedRealEndDate+"\n";
			//output += "realMapFusioned : "+realMapFusioned+"\n";
			
			return output;
		}
	}
	
}