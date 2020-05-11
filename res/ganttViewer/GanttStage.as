package ganttViewer{
    import flash.text.*;
	import flash.display.*;
	import flash.events.*;
	import flash.geom.*;
	import fl.transitions.*;
	import fl.transitions.easing.*;

	

    public class GanttStage extends Sprite {
		var stageBackground:Shape;
		var scaleFactor:Number=1;
		
		var stageColor:uint = 0xffffff;
		
		var visualAreaWidth:uint; //width of the flash applet
		var visualAreaHeight:uint; //height of the flash applet
		
		var graphicWidth:uint;	//width of the gantt graphics area
		var graphicHeight:uint; //height of the gantt graphics area
		
		var dragConstraint:Rectangle;
		
        public function GanttStage(visualAreaWidth:uint, visualAreaHeight:uint, daySize:Number, projectSize:Number, numDays:Number, numProjects:uint, leftMargin:Number, topMargin:uint) {
           
			this.visualAreaWidth = visualAreaWidth;
			this.visualAreaHeight = visualAreaHeight;
			
			graphicWidth = numDays*daySize;
			graphicHeight = numProjects*projectSize;
			
			//drawStage();
			
			//dragConstraint = new Rectangle(-(visualAreaWidth+graphicWidth), -(visualAreaHeight+graphicHeight),visualAreaWidth+graphicWidth,visualAreaHeight+graphicHeight );
		   	//dragConstraint = new Rectangle(-(1000), -(1000),1000,1000);
		   
		   
			//addEventListener(MouseEvent.MOUSE_WHEEL, mouseWheelHandler);
			//addEventListener(MouseEvent.DOUBLE_CLICK, doubleClickHandler);
			//addEventListener(MouseEvent.MOUSE_DOWN, mouseDownHandler);
			//addEventListener(MouseEvent.MOUSE_OVER, mouseOverHandler);
			//addEventListener(MouseEvent.MOUSE_UP, mouseUpHandler);
			//addEventListener(MouseEvent.MOUSE_OUT, mouseOutHandler);
        }

		
		
		/*		
		public function drawStage(){
			stageBackground = new Shape();
			stageBackground.graphics.beginFill(stageColor);
			stageBackground.graphics.drawRect(0,0, graphicWidth, graphicHeight);
			
			//stageBackground.graphics.drawRect(-visualAreaWidth,-visualAreaHeight, visualAreaWidth*3+graphicWidth, visualAreaHeight*3+graphicHeight);
			stageBackground.graphics.endFill();
			addChild(stageBackground);
			
			trace("graphicWidth="+graphicWidth);	
			trace("graphicHeight="+graphicHeight);
			
		}*/
		
		
		private function mouseWheelHandler(event:MouseEvent):void {
			trace("mouseWheelHandler delta: " + event.delta);
			scaleFactor = scaleFactor + event.delta/20
			if(scaleFactor <1)scaleFactor=1;
			scaleX = scaleFactor;
			scaleY = scaleFactor;
			
		}
		
		public function zoomIn():void {
			scaleFactor = scaleFactor + 5/20
			if(scaleFactor <1)scaleFactor=1;
			//scaleX = scaleFactor;
			//scaleY = scaleFactor;
			new fl.transitions.Tween(this , "scaleX" , Back.easeOut ,this.scaleX , scaleFactor , 6 , false);
			new fl.transitions.Tween(this , "scaleY" , Back.easeOut ,this.scaleY , scaleFactor , 6 , false);
		}
		
		public function zoomOut():void {
			scaleFactor = scaleFactor + -5/20
			if(scaleFactor <1)scaleFactor=1;
			//scaleX = scaleFactor;
			//scaleY = scaleFactor;
			new fl.transitions.Tween(this , "scaleX" , Back.easeOut ,this.scaleX , scaleFactor , 6 , false);
			new fl.transitions.Tween(this , "scaleY" , Back.easeOut ,this.scaleY , scaleFactor , 6 , false);
		}
		
		private function doubleClickHandler(event:MouseEvent):void {
			scaleFactor=1;
			//scaleX = scaleFactor;
			//scaleY = scaleFactor;
			new fl.transitions.Tween(this , "scaleX" , Back.easeOut ,this.scaleX , scaleFactor , 6 , false);
			new fl.transitions.Tween(this , "scaleY" , Back.easeOut ,this.scaleY , scaleFactor , 6 , false);
		}
		
		public function moveLeft():void {
			//x -= 40;
			new fl.transitions.Tween(this , "x" , Back.easeOut ,this.x , this.x-60 , 6 , false);
									
		}
		
		public function moveRight():void {
			//x += 40;
			new fl.transitions.Tween(this , "x" , Back.easeOut ,this.x , this.x+60 , 6 , false);
		}
		
		public function moveUp():void {
			//y -= 40;
			new fl.transitions.Tween(this , "y" , Back.easeOut ,this.y , this.y-60 , 6 , false);
		}
		
		public function moveDown():void {
			//y += 40;
			new fl.transitions.Tween(this , "y" , Back.easeOut ,this.y , this.y+60 , 6 , false);
		}
		
		/*
		private function mouseDownHandler(event:MouseEvent):void {
			startDrag(false,dragConstraint);			
		}
		
		
		private function mouseUpHandler(event:MouseEvent):void {
			stopDrag();			
		}
		
		private function mouseOverHandler(event:MouseEvent):void {
			
			
		}
		
		private function mouseOutHandler(event:MouseEvent):void {
			
			//stopDrag();
		}*/





    }
}