package ganttViewer{
	import flash.display.*;
	import flash.geom.Matrix;

	import flash.text.TextField;
	
	public class GanttBar extends Sprite{
		
		public function GanttBar(){
			/*
			var red:Shape = createCircle(0xFF0000,10);
			red.x=10;
			red.y=20;
			addChild(red);
			
			var hello:TextField = new TextField();
			hello.text = "hello";
			hello.x=10;
			hello.y=20;
			//DisplayObjectContainer(root).
			addChild(hello);
			trace("hello");*/
			
			drawBar();
		}
		
		public function createCircle(color:uint, radius:Number):Shape{
			var shape:Shape = new Shape();
			shape.graphics.beginFill(color);
			shape.graphics.drawCircle(0,0, radius);
			shape.graphics.endFill();
			return shape;
		}
		
		public function drawBar(){
			var type:String = GradientType.LINEAR;
			var colors:Array = [0xe4e4e4, 0xa6a6a6];
			var alphas:Array = [1, 1];
			var ratios:Array = [0, 255];
			var spreadMethod:String = SpreadMethod.PAD;
			var interp:String = InterpolationMethod.LINEAR_RGB;
			var focalPtRatio:Number = 0;
			
			var matrix:Matrix = new Matrix();
			var boxWidth:Number = 100;
			var boxHeight:Number = 25;
			var boxRotation:Number = Math.PI/2; // 90˚
			var tx:Number = 25;
			var ty:Number = 0;
			matrix.createGradientBox(boxWidth, boxHeight, boxRotation, tx, ty);
			
			var square:Shape = new Shape;
			square.graphics.beginGradientFill(type, 
										colors,
										alphas,
										ratios, 
										matrix, 
										spreadMethod, 
										interp, 
										focalPtRatio);
			square.graphics.drawRect(0, 0, 100, 25);
			addChild(square);

		}
	}
	
}