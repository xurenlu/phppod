<?php
/**
PHPCL_ESC 即是ESC,
输入方法:Ctl V ,ESC
八进制是033
十进制是27
*/
define("PHPCL_ESC",chr(27));
define("PHPCL_OFF",0);
define("PHPCL_HIGHLIGHT",1);
define("PHPCL_UNDERLINE",4);
define("PHPCL_FLICKER",5);
define("PHPCL_INVERSE",7);
define("PHPCL_HIDDEN",8);

define("PHPCL_BLACK",0);
define("PHPCL_RED",1);
define("PHPCL_GREEN",2);
define("PHPCL_YELLOW",3);
define("PHPCL_BLUE",4);
define("PHPCL_MAUVE",5);
define("PHPCL_CYAN",6);
define("PHPCL_WHITE",7);

/**
0 OFF
1 高亮显示
4 underline
5 闪烁
7 反白显示
8 不可见
*/
$attrs=array(0,1,4,5,7);
/**
文字色:
30:黑色
31:red
32:green
33:傻不垃圾的土黄色..
34:蓝色
35:紫色
36:Cyan
37:灰白
*/
$fores=array(30,31,32,33,34,35,36,37);
/**
背景:
40:黑色
41:red
42:green
43:傻不垃圾的土黄色..
44:蓝色
45:紫色
46:Cyan
47:灰白
*/
$backs=array(40,41,42,43,44,45,46,47);

function phpcl_str($str,$attr,$fore,$back){
	$fore=$fore+30;
	$back=$back+40;
	$tmp=PHPCL_ESC."[".$attr.";".$fore.";".$back."m";
	$tmp.=$str;
	$tmp.=PHPCL_ESC."[0;0;0m";
	return $tmp;
}
/**
@example:
$argc=$_SERVER["argc"];
$argv=$_SERVER["argv"];
print phpcl_str("hello",PHPCL_UNDERLINE,PHPCL_YELLOW,PHPCL_BLACK);
*/
?>

