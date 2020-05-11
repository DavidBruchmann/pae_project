package ganttViewer
{
 import fl.core.UIComponent;
 //import mx.core.IDataRenderer;
 import flash.display.Graphics;
 //import mx.controls.listClasses.IListItemRenderer;
 //import mx.utils.GraphicsUtil;
 import flash.geom.Rectangle;
 //import util.SimpleGanttUtil;
 import flash.events.Event;
 //import mx.events.FlexEvent;
 //import mx.controls.listClasses.BaseListData;
 //import mx.controls.dataGridClasses.DataGridListData;
 
 
 public class ganttRenderer extends UIComponent 
 {
  private var _data : Object = null;
  
  
  public function get data():Object
  {
   return _data; 
  }
 
  public function set data(value:Object):void
  {
   this._data = value;
   this.invalidateProperties();
   
   dispatchEvent(new Event(Event.DATA_CHANGE));
  }
  
  override protected function updateDisplayList(w:Number, h:Number):void
  {
   super.updateDisplayList(w, h);
   
   var g:Graphics = graphics;
  
   g.clear();
   
   if ( _data != null)
   {
    g.lineStyle(1, 0x000000, 1);
    g.beginFill(0xFF0000, .5);
   
    var r:Rectangle = calculateRectangle(w,h);
    
    GraphicsUtil.drawRoundRectComplex(g, r.x, r.y, r.width, r.height, 0, 0, 0, 0);
    g.endFill();
   }
  }
  
  private function calculateRectangle(w:Number, h:Number) : Rectangle
  {
	  var duration =10;
   var xmlData : XML = XML(_data);
   
   var rect_x : int = 1+ ((w-2) * (xmlData.@start/duration));
   var rect_y : int = 1;
   var rect_width  : int = (w-2) * (xmlData.@duration/duration);
   var rect_height : int = h-2;
   
   return new Rectangle( rect_x, rect_y, rect_width, rect_height );
  }
  
 }
}