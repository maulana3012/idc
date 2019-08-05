// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
// ===================================================================
var MONTH_NAMES=new Array('January','February','March','April','May','June','July','August','September','October','November','December','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');var DAY_NAMES=new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sun','Mon','Tue','Wed','Thu','Fri','Sat');
function LZ(x){return(x<0||x>9?"":"0")+x}
function isDate(val,format){var date=getDateFromFormat(val,format);if(date==0){return false;}return true;}
function compareDates(date1,dateformat1,date2,dateformat2){var d1=getDateFromFormat(date1,dateformat1);var d2=getDateFromFormat(date2,dateformat2);if(d1==0 || d2==0){return -1;}else if(d1 > d2){return 1;}return 0;}
function formatDate(date,format){format=format+"";var result="";var i_format=0;var c="";var token="";var y=date.getYear()+"";var M=date.getMonth()+1;var d=date.getDate();var E=date.getDay();var H=date.getHours();var m=date.getMinutes();var s=date.getSeconds();var yyyy,yy,MMM,MM,dd,hh,h,mm,ss,ampm,HH,H,KK,K,kk,k;var value=new Object();if(y.length < 4){y=""+(y-0+1900);}value["y"]=""+y;value["yyyy"]=y;value["yy"]=y.substring(2,4);value["M"]=M;value["MM"]=LZ(M);value["MMM"]=MONTH_NAMES[M-1];value["NNN"]=MONTH_NAMES[M+11];value["d"]=d;value["dd"]=LZ(d);value["E"]=DAY_NAMES[E+7];value["EE"]=DAY_NAMES[E];value["H"]=H;value["HH"]=LZ(H);if(H==0){value["h"]=12;}else if(H>12){value["h"]=H-12;}else{value["h"]=H;}value["hh"]=LZ(value["h"]);if(H>11){value["K"]=H-12;}else{value["K"]=H;}value["k"]=H+1;value["KK"]=LZ(value["K"]);value["kk"]=LZ(value["k"]);if(H > 11){value["a"]="PM";}else{value["a"]="AM";}value["m"]=m;value["mm"]=LZ(m);value["s"]=s;value["ss"]=LZ(s);while(i_format < format.length){c=format.charAt(i_format);token="";while((format.charAt(i_format)==c) &&(i_format < format.length)){token += format.charAt(i_format++);}if(value[token] != null){result=result + value[token];}else{result=result + token;}}return result;}
function _isInteger(val){var digits="1234567890";for(var i=0;i < val.length;i++){if(digits.indexOf(val.charAt(i))==-1){return false;}}return true;}
function _getInt(str,i,minlength,maxlength){for(var x=maxlength;x>=minlength;x--){var token=str.substring(i,i+x);if(token.length < minlength){return null;}if(_isInteger(token)){return token;}}return null;}
function getDateFromFormat(val,format){val=val+"";format=format+"";var i_val=0;var i_format=0;var c="";var token="";var token2="";var x,y;var now=new Date();var year=now.getYear();var month=now.getMonth()+1;var date=1;var hh=now.getHours();var mm=now.getMinutes();var ss=now.getSeconds();var ampm="";while(i_format < format.length){c=format.charAt(i_format);token="";while((format.charAt(i_format)==c) &&(i_format < format.length)){token += format.charAt(i_format++);}if(token=="yyyy" || token=="yy" || token=="y"){if(token=="yyyy"){x=4;y=4;}if(token=="yy"){x=2;y=2;}if(token=="y"){x=2;y=4;}year=_getInt(val,i_val,x,y);if(year==null){return 0;}i_val += year.length;if(year.length==2){if(year > 70){year=1900+(year-0);}else{year=2000+(year-0);}}}else if(token=="MMM"||token=="NNN"){month=0;for(var i=0;i<MONTH_NAMES.length;i++){var month_name=MONTH_NAMES[i];if(val.substring(i_val,i_val+month_name.length).toLowerCase()==month_name.toLowerCase()){if(token=="MMM"||(token=="NNN"&&i>11)){month=i+1;if(month>12){month -= 12;}i_val += month_name.length;break;}}}if((month < 1)||(month>12)){return 0;}}else if(token=="EE"||token=="E"){for(var i=0;i<DAY_NAMES.length;i++){var day_name=DAY_NAMES[i];if(val.substring(i_val,i_val+day_name.length).toLowerCase()==day_name.toLowerCase()){i_val += day_name.length;break;}}}else if(token=="MM"||token=="M"){month=_getInt(val,i_val,token.length,2);if(month==null||(month<1)||(month>12)){return 0;}i_val+=month.length;}else if(token=="dd"||token=="d"){date=_getInt(val,i_val,token.length,2);if(date==null||(date<1)||(date>31)){return 0;}i_val+=date.length;}else if(token=="hh"||token=="h"){hh=_getInt(val,i_val,token.length,2);if(hh==null||(hh<1)||(hh>12)){return 0;}i_val+=hh.length;}else if(token=="HH"||token=="H"){hh=_getInt(val,i_val,token.length,2);if(hh==null||(hh<0)||(hh>23)){return 0;}i_val+=hh.length;}else if(token=="KK"||token=="K"){hh=_getInt(val,i_val,token.length,2);if(hh==null||(hh<0)||(hh>11)){return 0;}i_val+=hh.length;}else if(token=="kk"||token=="k"){hh=_getInt(val,i_val,token.length,2);if(hh==null||(hh<1)||(hh>24)){return 0;}i_val+=hh.length;hh--;}else if(token=="mm"||token=="m"){mm=_getInt(val,i_val,token.length,2);if(mm==null||(mm<0)||(mm>59)){return 0;}i_val+=mm.length;}else if(token=="ss"||token=="s"){ss=_getInt(val,i_val,token.length,2);if(ss==null||(ss<0)||(ss>59)){return 0;}i_val+=ss.length;}else if(token=="a"){if(val.substring(i_val,i_val+2).toLowerCase()=="am"){ampm="AM";}else if(val.substring(i_val,i_val+2).toLowerCase()=="pm"){ampm="PM";}else{return 0;}i_val+=2;}else{if(val.substring(i_val,i_val+token.length)!=token){return 0;}else{i_val+=token.length;}}}if(i_val != val.length){return 0;}if(month==2){if( ((year%4==0)&&(year%100 != 0) ) ||(year%400==0) ){if(date > 29){return 0;}}else{if(date > 28){return 0;}}}if((month==4)||(month==6)||(month==9)||(month==11)){if(date > 30){return 0;}}if(hh<12 && ampm=="PM"){hh=hh-0+12;}else if(hh>11 && ampm=="AM"){hh-=12;}var newdate=new Date(year,month-1,date,hh,mm,ss);return newdate.getTime();}
function parseDate(val){var preferEuro=(arguments.length==2)?arguments[1]:false;generalFormats=new Array('y-M-d','MMM d, y','MMM d,y','y-MMM-d','d-MMM-y','MMM d');monthFirst=new Array('M/d/y','M-d-y','M.d.y','MMM-d','M/d','M-d');dateFirst =new Array('d/M/y','d-M-y','d.M.y','d-MMM','d/M','d-M');var checkList=new Array('generalFormats',preferEuro?'dateFirst':'monthFirst',preferEuro?'monthFirst':'dateFirst');var d=null;for(var i=0;i<checkList.length;i++){var l=window[checkList[i]];for(var j=0;j<l.length;j++){d=getDateFromFormat(val,l[j]);if(d!=0){return new Date(d);}}}return null;}


var n4 = (document.layers) ? true : false;
var e4 = (document.all) ? true : false;

/****************************************************************/
/** Regular Expression                                         **/
/** usage : reDigit.test(value)                                **/
/** usage : reAlpha.test(value)                                **/
/** usage : reAlphaNumeric.test(value)                         **/
/** return  boolean                                            **/
/****************************************************************/
var reDigit = /^\d+$/;							//	isDigit
var reAccount = /^[0-9]+$/i;					//	isAccount(����)
var reAlpha = /^[a-z]+$/i;						//	isAlpha(ignore case)
var reAlphaBlank = /^([a-z]|[\' \'])+$/i;		//	isAlphaBlank(ignore case)
var reNumeric = /^[0-9]+$/;						//	isNumeric
var reNumericCurrency = /^([0-9]|[-.,+])+$/i;	//	isNumericCurrency
var reAlphaNumeric = /^([a-z]|[0-9])+$/i;		//	isAlphaNumeric
var reAlphaNumericEtc = /^([a-z]|[0-9]|[-.,+\(\)\' \'\:])+$/i;		//	isAlphaNumericEtc(����,����,����Ư������:��ȯ���)
var reAlphaNumericBlank = /^([a-z]|[0-9]|[\' \'])+$/i;				//	isAlphaNumericEtc(����,����,blank:��ȯ���)
var reNumericPhone = /^([0-9]|[\' \'])+$/i;							//	reNumericPhone(����,blank:��ȯ���)

var reAlphaNumericEtc2 = /^([a-z]|[0-9]|[-.,+\_\(\)\' \'\:])+$/i;	//	isAlphaNumericEtc2(����,����,����Ư������:email�ּ��Է½�)

var wloc = window.location.href;

var isEng = true;
if(wloc.indexOf('/eng/') > -1){
  isEng = true;
}

var onProcessing = null;
var isExport = false;

	function get_GET() {
		var args = new Array();
		var query = window.location.search.substring(1);
		var pairs = query.split("&");
		for (var i = 0; i < pairs.length; i++) {
			var pos = pairs[i].indexOf('=');
			if (pos == -1) continue;
			var argname = pairs[i].substring(0,pos);
			var value = pairs[i].substring(pos+1);
	
			args[argname] = unescape(value);
		}
	
		return args;
	}
		

	
	/**
	 *    Extract the value of a key in a query string
	 *    @parm srhStr : The query string (ex. http://www.zonekom.com/text?name=me) (required)
	 *    @parm keyStr : The key name (required)
	 */
	function getValue(srhStr,keyStr) {
		if( (p = srhStr.indexOf("?" + keyStr + "=")) == -1){
			if( (p = srhStr.indexOf("&" + keyStr + "=")) == -1)
				return '';
		}

		srhStr = srhStr.substring(p + keyStr.length + 2);
		 if(srhStr == '') {
			return '';
		}
			
		if( (p = srhStr.indexOf('&')) == -1) {
			return srhStr;
		}
		
		return srhStr.substring(0,p);
	}

	/**
	 *    Get the value of a cookie whose name is cookieName
	 *    @parm cookieName : The name of the cookie (required)
	 */
	function GetCookie(cookieName) {
		var nameLen = cookieName.length;
		var cookieStr = document.cookie;
		var cLen = document.cookie.length;
		var i=0;
		if( (p = cookieStr.indexOf(cookieName)) == -1) {
			return null;
		}
			
		cookieStr = cookieStr.substring(p + nameLen + 1);

		if(cookieStr == '') {
			return '';
		}
		
		if( (p = cookieStr.indexOf(';')) == -1) {
			return cookieStr;
		}
		
		return cookieStr.substring(0,p);
	}

	/***
	 *    Extract the domain name from current url
	 */
	function getDomain(){
		Loc = document.location.href;
		Loc = Loc.substring(Loc.indexOf('.') + 1);
		return Loc.substring(0,Loc.indexOf('/'));
	}

	/**
	 *    Set a cookie 
	 *    @parm cname   cookie name   (required)
	 *    @parm cvalue  cookie value  (required)
	 *    @parm cpath   cookie path   (optional)
	 *    @parm cexpire cookie expire date  (optional)
	 */
	function setcookie(cname,cvalue,cpath,cexpire){
		if(typeof(document.domain) == 'undefined') {
			cdomain = getDomain();
		} else if(document.domain == '') {
			cdomain = getDomain();
		} else  {
			cdomain = document.domain;
		}
		
		var path = (typeof(cpath) == 'undefined') ? '/' : cpath;
		var today = (typeof(cexpire) == 'undefined') ? new Date() : cexpire;

		document.cookie = cname + "=" + cvalue
						+ "; expires=" + today.toGMTString()
						+ ((path == null) ? "" : ("; path=" + path))
						+ ((cdomain == null) ? "" : ("; domain=" + cdomain));
	}

	/***
	 *    ���ڸ� �ݾ� ���·� ��������
	 *    @param  frm_amt String �ݾ�
	 *    @param  len     Int �Ҽ��� �ڸ���
	 *    @return formatValue String ���ǵ� ����
	 */
	function numFormatval(frm_amt, len){

		if ( len==null )	{
			len = 0;
		}

		for(ci=0;ci<frm_amt.length;ci++){
			if (frm_amt.charAt(ci) == ','){
				frm_amt = removecomma(frm_amt);
				break;
			}
		}

		var zeroNum = 0;
		var zeroChk = false;

		for ( ci=0; ci<frm_amt.length; ci++ )	{
			if ( frm_amt.charAt(ci) != '0' )	{
				zeroNum = ci;
				zeroChk = true;
				break;
			}
		}
		if (zeroNum != 0)
			frm_amt = frm_amt.substr(zeroNum,frm_amt.length);
		else if ( zeroChk == false )
			frm_amt = "0";

		frm_amt = addcomma1(frm_amt, len);

		var dotPos = frm_amt.split(".")
		var dotU = dotPos[0];
		var dotD;
		if ( len>0 )	{
			dotD = dotPos[1];
		}

		if ( (dotU.replace("-","")).length==0 )	{
			dotU = "0";
		}

		if ( len>0 )	{
			if ( dotD.length < len )	{
				for ( d=0; len-dotD.length; d++ )	{	dotD = dotD+"0";	}
			}
			else if ( dotD.length > len )	{
				dotD = dotD.substr(0,len);
			}
		}

		if ( len>0 )	{
			rtnVal = dotU+"."+dotD
		}
		else	{
			rtnVal = dotU
		}
		return rtnVal;
	}

	/***
	 *    ���ڸ� �ݾ� ���·� �������� ( ��, �޸� ���� )
	 *    @param frm_amt String �ݾ�
	 *    @param len     Int �Ҽ��� �ڸ���
	 *    @return formatValue String ���ǵ� ����
	 */
	function numFormatRateval(frm_amt, len)	{
		var frm_amt = numFormatval(frm_amt,len);
		frm_amt = removecomma(frm_amt);

		return frm_amt;
	}

	/***
	 *    ���ڸ� �ݾ� ���·� �����Ͽ� ���� ��������
	 *    @param frm_amt String �ݾ�
	 *    @param len     Int �Ҽ��� �ڸ���
	 */
	function numFormatvalObj(obj, len)	{
		frm_amt = nfTrim(obj.value);

		if(frm_amt.length>0 && !reNumericCurrency.test(frm_amt))	{
			if ( !isEng )	{
				alert("�ݾ�ǥ�� ���ڸ� �Է��ϼ���. \nex) ����, Sign, �Ҽ���, �޸�");
			}	else	{
				alert("must be enterd in currency format. \nex) numeric, sign, point, commma");
			}
			obj.select();
			obj.focus();
			return;
		}
		else if ( frm_amt.length>0 )	{
			obj.value = numFormatval(frm_amt, len);
		}
	}

	/***
	 *    ��ȭ �ݾ� ���·� �����Ͽ� ���� ��������
	 *
	 *    @param frm_amt String �ݾ�
	 *    @param frm_amt String ��ȭ
	 */
	function CurrencyFormatAmt(obj, objCurr)	{
		frm_amt = nfTrim(obj.value);

		if (objCurr.value == 'KRW')
			len = 0;
		else
			len = 2;

		if(frm_amt.length>0 && !reNumericCurrency.test(frm_amt))	{
			if ( !isEng )	{
				alert("�ݾ�ǥ�� ���ڸ� �Է��ϼ���. \nex) ����, Sign, �Ҽ���, �޸�");
			}	else	{
				alert("must be enterd in currency format. \nex) numeric, sign, point, commma");
			}
			obj.select();
			obj.focus();
			return;
		}
		else if ( frm_amt.length>0 )	{
			obj.value = numFormatval(frm_amt, len);
		}
	}

	/***
	 *    ���ڸ� �ݾ� ���·� �����Ͽ� ���� �������� ( ��, �޸� ���� )
	 *    @param frm_amt String �ݾ�
	 *    @param len     Int �Ҽ��� �ڸ���
	 */
	function numFormatRatevalObj(obj, len)	{
		frm_amt = nfTrim(obj.value);

		if(frm_amt.length>0 && !reNumericCurrency.test(frm_amt))	{
			if ( !isEng )	{
				alert("�ݾ�ǥ�� ���ڸ� �Է��ϼ���. \nex) ����, Sign, �Ҽ���, �޸�");
			}	else	{
				alert("must be enterd in currency format \nex) numeric, sign, point, commma");
			}
			obj.select();
			obj.focus();
			return;
		}
		else if ( frm_amt.length>0 )	{
			obj.value = numFormatRateval(frm_amt, len);
		}
	}

	/**
	 *	�Ҽ����� ������ �ݾ�ǥ�� ���
	 *	@param  str         String �ݾ�
	 *	@param  DecimalLen  �Ҽ��� �ڸ���
	 */
	function addcomma1(str, DecimalLen){
		nstr = '';
		str = ''+str+'';
		minus = '';
		flootstr = '';
		if(str.charAt(0) == '-'){
			minus = '-';
			str = str.substring(1);
		}
		if(str.indexOf('.') > -1){
			flootstr = str.substring(str.indexOf('.'));
			str = str.substring(0,str.indexOf('.'));

			for(ci=flootstr.length;ci<DecimalLen+1;ci++){
				flootstr += '0';
			}
		}

		if(DecimalLen>0){
			if(flootstr.length == 0) {
				flootstr += '.';
				for(ci=0;ci<DecimalLen;ci++){
					flootstr += '0';
				}
			}
		}

		if(str.length < 4)
			return (minus + str + flootstr);

		c = str.length%3;
		for(ci=0;ci<str.length;ci++){
			if((ci % 3) == c && ci != 0)
				nstr += ',';

			nstr += str.charAt(ci);
		}

		if(minus.length==' ' && nstr.length==' ' && nstr.length==' '){
			return '0';
		} else {
			return (minus + nstr + flootstr);
		}
	}

	/***
	 *    ���ڸ� �ݾ� ���·� ��������
	 *    @parm str Number String to format (required)
	 */
	function addcomma(str){
		nstr = '';
		str = ''+str+'';
		minus = '';
		flootstr = '';
		if(str.charAt(0) == '-'){
			minus = '-';
			str = str.substring(1);
		}
		if(str.indexOf('.') > -1){
			flootstr = str.substring(str.indexOf('.'));
			str = str.substring(0,str.indexOf('.'));
		}
		if(str.length < 4)
			return (minus + str + flootstr);

		c = str.length%3;

		for(ci=0;ci<str.length;ci++){
			if((ci % 3) == c && ci != 0)
				nstr += ',';

			nstr += str.charAt(ci);
		}
		return (minus + nstr + flootstr);
	}

	/***
	 *    �ݾ� ������ ��������
	 *    @parm str comma String to unformat (required)
	 */
	function removecomma(commastr){
		nstr = '';
		for(ci=0;ci<commastr.length;ci++){
			if(commastr.charAt(ci) == ',')
				continue;
		else
			nstr += '' + commastr.charAt(ci);
		}
		return nstr;
	}

	/***
	 *    �ݾ� ������ ��������
	 *    @parm str comma String to unformat (required)
	 */
	function removecommapoint(commastr){
		nstr = '';
		for(ci=0;ci<commastr.length;ci++){
			if(commastr.charAt(ci) == ',')
				continue;
		else
			nstr += '' + commastr.charAt(ci);
		}
		return nstr;
	}

	/**
	 *    trim function
	 *    @parm str ��ȯ�� ���ڿ� (required)
	 */
	function nfTrim(str){
		str = str.replace(/^\s+/,"");   //left trim
		return str.replace(/\s+$/,"");  //right trim

	}

	/**
	 *    �ֹι�ȣ check
	 *    @parm regNo �ֹι�ȣ (required)
	 */
	function regChk( regNo ){

		var regNo;
		var socnoStr = (regNo.value).toString();

		a = socnoStr.substring(0, 1);
		b = socnoStr.substring(1, 2);
		c = socnoStr.substring(2, 3);
		d = socnoStr.substring(3, 4);
		e = socnoStr.substring(4, 5);
		f = socnoStr.substring(5, 6);
		g = socnoStr.substring(6, 7);
		h = socnoStr.substring(7, 8);
		i = socnoStr.substring(8, 9);
		j = socnoStr.substring(9, 10);
		k = socnoStr.substring(10, 11);
		l = socnoStr.substring(11, 12);
		m = socnoStr.substring(12, 13);

		temp=a*2+b*3+c*4+d*5+e*6+f*7+g*8+h*9+i*2+j*3+k*4+l*5;
		temp=temp%11;
		temp=11-temp;
		temp=temp%10;

		if(temp == m)	{
			return true;
		}
		else	{
			if ( !isEng )	{
				alert("�߸��� �ֹε�Ϲ�ȣ�Դϴ�.");
			}	else	{
				alert("Enter your register number.");
			}
			regNo.focus();
			return false;
		}
    }

	/**
	 *    ����ڹ�ȣ check
	 *    @parm venNum ����ڹ�ȣ 10�ڸ� (required)
	 */
	function venChk( venNum )	{

		var csNumber = venNum.value.replace(/(\,|\.|\-|\/)/g,"");
		var checkArray = new Array(1,3,7,1,3,7,1,3,5);
		var sum=0;

		for(idx=0 ; idx < 9 ; idx++)
			sum += csNumber.charAt(idx) * checkArray[idx];

		sum = sum + ((csNumber.charAt(8) * 5 ) / 10);

		var nam = Math.floor(sum) % 10;

		var checkDigit = ( nam == 0 ) ? 0 : 10 - nam;

		numChk = (csNumber.toString() && !/\D/.test(csNumber));

		if ( !numChk || csNumber.charAt(9) != checkDigit)  {
			if ( !isEng )	{
				alert("�Է��� ����� ��Ϲ�ȣ�� �߸��Ǿ����ϴ�.\n\n�ٽ� �Է��� �ֽñ� �ٶ��ϴ�.");
			}	else	{
				alert("Enter your business number.");
			}
			venNum.focus();
			return false;
		}
		else	{
			return true;
		}

    }


	/*********** for verify *************/
	/**
	 *    Check all required form input ( which is marked by it's class name - req... ) 
	 *    @parm theForm : The form object reference ( ex. window.document.frmTest )
	 */
	function verify(theForm){
		eLen = theForm.elements.length;
		fmt = '';
		for(i=0;i<eLen;i++){
			// only check the required field
			if(theForm.elements[i].className.indexOf('req') > -1){
				if(theForm.elements[i].tagName == 'select'){
					if(nfTrim(theForm.elements[i].options[theForm.elements[i].selectedIndex].value) == ''){
						return putMessage(theForm.elements[i],'','');
					}
				} else {
					fmt = theForm.elements[i].className.substring(3);
					if(nfTrim(theForm.elements[i].value) == ''){
						return putMessage(theForm.elements[i],'','');
					}
				}
			} else if(theForm.elements[i].className.indexOf('fmt') > -1) { // check if this input should follow any special formatting
				fmt = theForm.elements[i].className.substring(3);
			} else {
				fmt = '';
			}

			if(fmt != ''){
				if(!formatCheck(theForm.elements[i], fmt)){
					return putMessage(theForm.elements[i],'1',fmt);
				}
			}
		}
		return true;
	}

	/***
	 *    Check the formatting of a form input ( which is marked by it's class name - fmt... ) . 
	 *    This function is called from verify()
	 *    @parm tElm : The form element that needs to be checked
	 *    @parm fmt  : Format type (required) : d:date, n:numeric, etc
	 */
	function formatCheck(tElm,fmt){
		chkVal = tElm.value;

		if(nfTrim(chkVal) == "") {
			return true;
		}
		
		switch(fmt){
			case 'd' :		return checkDate(tElm);
				break;
			case 'n' :		return checkNumber(tElm);
				break;
			case 'c' :		return checkAccount(tElm);
				break;
			case 'nc' :		return checkNumericCurrency(tElm);
				break;
			case 'l' :		return checkMaxLength(tElm);
				break;
			case 'a' :		return checkAlpha(tElm);
				break;
			case 'ab' :		return checkAlphaBlank(tElm);
				break;
			case 'an' :		return checkAlphaNumeric(tElm);
				break;
			case 'anl' :	return checkAlphaNumericLength(tElm);
				break;
			case 'anb' :	return checkAlphaNumericBlank(tElm);
				break;
			case 'ane' :	return checkAlphaNumericEtc(tElm);
				break;
			case 'ane2' :	return checkAlphaNumericEtc2(tElm);
				break;
			case 'p' :		return checkPassword(tElm);
				break;
			case 'e' :		return checkMailAddress(tElm);
				break;
			case 'h' :		return checkReNumericPhone(tElm);
				break;

			default : return true;
		}
	}

	/**
	 *    css �� �����Ͽ� E-Mail Address check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkMailAddress(tElm) {
		var regex = /^[-_.a-z0-9]+@(([-a-z0-9]+\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i;
		return regex.test(trim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� ��й�ȣ 4�ڸ� ���� check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkPassword(tElm){
		if(tElm.value.length != tElm.maxLength)
			return false;
		return reDigit.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� ���¹�ȣ 11�ڸ� ���� check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAccount(tElm){
		if(tElm.value.length != tElm.maxLength)
			return false;
		return reAccount.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� �ݾ�ǥ�� check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkNumericCurrency(tElm){
		return reNumericCurrency.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� Alpha check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlpha(tElm){
		return reAlpha.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� AlphaBlank check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlphaBlank(tElm){
		return reAlphaBlank.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� AlphaNumeric Blank check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlphaNumericBlank(tElm){
		return reAlphaNumericBlank.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� AlphaNumeric Blank check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkReNumericPhone(tElm){
		return reNumericPhone.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� AlphaNumeric check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlphaNumeric(tElm){
		return reAlphaNumeric.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� ����,���� 15�ڸ��Ǵ� MaxLength check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlphaNumericLength(tElm){
		if(tElm.value.length != tElm.maxLength)
		return false;
		return reAlphaNumeric.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� AlphaNumeric check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlphaNumericEtc(tElm){
		return reAlphaNumericEtc.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� AlphaNumeric check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkAlphaNumericEtc2(tElm){
		return reAlphaNumericEtc2.test(nfTrim(tElm.value));
	}

	/**
	 *    css �� �����Ͽ� Max Length check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkMaxLength(tElm){
		tElm.value = nfTrim(tElm.value);
		chkVal = tElm.value;
		maxLen = tElm.maxLength;

		if(chkVal == "")
			return true;
		vLen = 0;

		for(len=0;len<chkVal.length;len++){
			if(chkVal.charCodeAt(len) > 10000){
				vLen++;
			}
			vLen++;
		}
		if(vLen > maxLen){
			return false;
		}
		return true;
	}

	/**
	 *    css �� �����Ͽ� date format check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */

	function checkDate(tElm){
		chkVal = tElm.value;
		var d = parseDate(chkVal, 'prefer_euro_format');
		if(d == null) {
			return false;
		} else {
			tElm.value = formatDate(d, 'yyyy-MM-dd');
			return true;
		}
	}

	/**
	 *    css �� �����Ͽ� number format check -- formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 */
	function checkNumber(tElm){
		chkVal = tElm.value;
		if(chkVal.indexOf(',') > -1){
			tElm.value = removecomma(tElm.value);
			chkVal = tElm.value;
		}
		if(isNaN(Number(chkVal))){
			return false;
		}
		return true;
	}

	/**
	 *    �ʼ��Է�,format check ���� �޼��� ��� -- verify(),formatCheck() ���� �ҷ���
	 *    @parm tElm target Element Object (required)
	 *    @parm flag (required): '':�ʼ��Է� ����, '1':format ����
	 *    @parm fmt format type (required) : d:date, n:numeric
	 */
	function putMessage(tElm,flag,fmt){

		if (tElm.parentNode.previousSibling.innerText != null) {
			tTxt = tElm.parentNode.previousSibling.innerText;
		} else if (tElm.parentNode.previousSibling.nodeValue != null) {
			tTxt = tElm.parentNode.previousSibling.nodeValue;
		}

//		alert("tElm.parentNode.previousSibling.innerText>>>"+ tElm.parentNode.previousSibling.innerText);

		var tTxt = 'The Data';

		if (tElm.parentNode.previousSibling != null) {
			tTxt = tElm.parentNode.previousSibling.innerText;
		} else if (tElm.parentNode.previousSibling != null) {
			tTxt = tElm.parentNode.previousSibling.nodeValue;
		}

		if((bp = tTxt.indexOf('<br>')) > -1) {
			tTxt = tTxt.substring(0,bp) + ' ' + tTxt.substring(bp+4);
		}
		
		pTxt = ''; // for korean
		eTxt = ''; // for English

		if(flag == ''){
			if(isEng){
				alert(tTxt + ' must be entered.');
			} else {
				alert(tTxt + '��(��) �ʼ� �Է��Դϴ�');
			}
		}
		else{
		switch(fmt){
			case 'd' :
							pTxt = tTxt + ' ��(��) ��¥�������� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered with proper date format';
							break;
			case 'n' :		pTxt = tTxt + ' ��(��) ���ڷθ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in digit numbers only.';
							break;
			case 'c' :		if(typeof(tElm.maxLength) == 'undefined')
								maxLen = 11;
							else
								maxLen = tElm.maxLength;
							pTxt = tTxt + ' ��(��) ���� ' + maxLen + ' �ڸ��� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be ' + maxLen + ' digit numbers.';
							break;
			case 'nc' :		pTxt = tTxt + ' ��(��) �ݾ�ǥ�� ������ ���ڸ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in digit amount type only.';
							break;
			case 'a' :		pTxt = tTxt + ' ��(��) �����ڸ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in a combination of letter alphabets only.';
							break;
			case 'ab' :		pTxt = tTxt + ' ��(��) �����ڿ� �������θ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in a combination of letter alphabets and blank only.';
							break;
			case 'an' :		pTxt = tTxt + ' ��(��) �����ڿ� ���ڷθ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in a combination of letter alphabets and digit numbers only.';
							break;
			case 'anl' :	if(typeof(tElm.maxLength) == 'undefined')
								maxLen = 13;
							else
								maxLen = tElm.maxLength;
							pTxt = tTxt + ' ��(��) �����ڿ� ���� ' + maxLen + ' �ڸ��� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be ' + maxLen + ' alphabets and digit numbers.';
							break;
			case 'anb' :	pTxt = tTxt + ' ��(��) �����ڿ� ����, �������θ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in a combination of letter alphabets and digit numbers and blank only.';
							break;
			case 'ane' :	pTxt = tTxt + ' ��(��) ����, ���� .,-+:()�θ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in alphabets, digit numbers and .,-+:() only.';
							break;
			case 'ane2' :	pTxt = tTxt + ' ��(��) ����, ���� .,-+_:()�θ� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be entered in alphabets, digit numbers and .,-+_:() only.';
							break;
			case 'p' :		if(typeof(tElm.maxLength) == 'undefined')
								maxLen = 4;
							else
								maxLen = tElm.maxLength;
							pTxt = tTxt + ' ��(��) ���� ' + maxLen + ' �ڸ��� �Է��ϼž� �˴ϴ�.';
							eTxt = tTxt + ' must be ' + maxLen + ' digit numbers.';
							break;
			case 'l' :		if(typeof(tElm.maxLength) == 'undefined')
								maxLen = 0;
							else
								maxLen = tElm.maxLength;
							pTxt = tTxt + ' �� ���� ���ѱ��̸� �ʰ��߽��ϴ�. ' + maxLen + '���ڸ� �Է��Ͻ� �� �ֽ��ϴ�.';
							eTxt = tTxt + ' has exceeded the required length field.  Please enter only ' + maxLen + ' letters.';
							break;
			case 'e' :		pTxt = tTxt + ' ��(��) E-Mail ���Ŀ� �´� Address �� �Է��ϼž� �˴ϴ�.';
							eTxt = 'Enter your email address for ' + tTxt + '.';
							break;
			case 'h' :		pTxt = tTxt + ' ��(��) ���� �����̽��� �Է��ϼž� �˴ϴ�.';
							eTxt = 'must be entered in digit numbers and space only.';
							break;

			default :		break;
			}
			
			if(isEng){
				alert(eTxt);
			} else {
				alert(pTxt);
			}
		}
		tElm.focus();
		return false;
	}

	/**
	 *	���� �ڸ������� 3�ڸ��� ���� ,�� �־��ش�. ��ü�� �ݾ�ó��
	 */
	function numFormat(obj, msgchk){
		if(!reNumeric.test(obj.value) && nfTrim(obj.value) != '' && typeof(msgchk) == 'undefined') {
			if(!isEng) alert('���ڸ� �Է��Ͻʽÿ�.');
			else alert('must be entered in digit numbers only.');

			obj.value = '';
			obj.focus();
			return;
		}
		if(obj.value.indexOf(',') > -1){
			obj.value = removecomma(obj.value);
		}
		obj.value = addcomma(obj.value);
	}

	/**
	 *	������ format�� ����. ��ü�� �ݾ�ó��
	 */
	function numUnformat(obj) {
		obj.value = removecomma(obj.value);
	}

	/**
	 *	������ format�� ������ return
	 */
	function numUnformat1(obj) {
		return removecomma(obj.value);
	}
	/**
	 *	������ format�� ������ return
	 *	�ش� ��ü�� value�� ��������
	 */
	function numUnformat2(obj) {
		obj.value = removecomma(obj.value);
		return obj.value;
	}

	/**
	 *	���� ��ũ�� ����Ҷ� ���
	 */
	function makeUrl(URL, frameName)	{
		if ( frameName==null )
			parent.leftFrame.location.href=URL;
		else
			eval("parent."+frameName+".location.href=URL");
	}

	/**
	 *	IniTech ���ȸ���� ����Ͽ� ��ũ�� Popup�� �Ҷ� ���
	 *	popupOption�� ������ frameName�� popup�� ����.
	 */
	function makeINIUrl(URL, parameter, frameName, popupOption)	{

		if ( frameName==null )
			frameName = 'mainFrame';
		if ( popupOption==null )
			popupOption = '';
		if ( parameter==null )
			parameter = '';
		parameter = parameter + "&randomCashKey=" + getRandom();

		return EncLink(URL, parameter,frameName, popupOption);
	}

	/**
	 *	Random�� ���ڸ� return���ش�.
	 */
	function getRandom()	{
		randomKey = Math.random()+"";

		if ( (pos = randomKey.indexOf('.')) > -1 )	{	//	���Եȴٸ�
			randomKey = randomKey.substring(pos+1, randomKey.length);
		}

		return randomKey;
	}

	/**
	 *	Refresh�� �������� ���� �ð��� �����´�.
	 */
	function getOrderTime()	{

		var dateObj = new Date();
		var date_str = "";

		if( dateObj.getHours() < 10 )
			date_str += "0" + (dateObj.getHours());
		else
			date_str += ""+dateObj.getHours();

		if( dateObj.getMinutes() < 10 )
			date_str += "0" + dateObj.getMinutes();
		else
			date_str += ""+dateObj.getMinutes();

		if( dateObj.getMilliseconds() < 10 )
			date_str += "00" + dateObj.getMilliseconds();
		else if ( dateObj.getMilliseconds() < 100 )
			date_str += "0" + dateObj.getMilliseconds();
		else
			date_str += ""+dateObj.getMilliseconds();

		return date_str;
	}

	/**
	 *	���ڿ��� ���ڿ��� ���ڿ��� ��ȯ ���ٶ� ���
	 */
	function replaceString(fullS,oldS,newS)	{
		//	fullS : ����,  oldS : �ٲ�,  newS : �ٲܲ�
		//	Replaces oldS with newS in the string fullS
		if ( oldS==null || newS==null )	{
			if (!isEng)	{
				alert('replaceString�� �߸�����ϼ̽��ϴ�.');
			}
			return;
		}
		else	{
			for (var i=0; i<fullS.length; i++) {
				if (fullS.substring(i,i+oldS.length) == oldS) {
					fullS = fullS.substring(0,i)+newS+fullS.substring(i+oldS.length,fullS.length)
				}
			}
		}
		return fullS	//	��ȯ�� ���� ���ڿ�
	}

	/**
	 *	���ڿ��� ����ڷ� �ٲ��ش�.
	 */
	function setUpperCase(obj)	{
		var obj;

		if ( obj != null )	{
			obj.value = obj.value.toUpperCase();
			return true;
		}
		else	{
			return false;
		}
	}

	/**
	 *	���ڿ��� �ҹ��ڷ� �ٲ��ش�.
	 */
	function setLowerCase(obj)	{
		var obj;

		if ( obj != null )	{
			obj.value = obj.value.toLowerCase();
			return true;
		}
		else	{
			return false;
		}
	}

	/**
	 *	���� ���� �ĸ�(,)�� �����Ͽ� ���ڸ� ��󳽴�.
	 */
	function newNumber(valStr)	{
		var valStr;
		var valNum;
		valStr = valStr.toString().replace(/\,| /g,'');
		valNum = eval(new Number(valStr));
		if ( isNaN(valNum) )	return 0;
		else	return valNum;
	}

	/**
	 *	���� ���� �Ҽ��κ��� �����Ͽ� �����κи� ��󳽴�.
	 *	ex1) getInt(this,2)
	 *	ex2) getInt('1010.22')	=>	1010
	 */
	function getInt(obj)	{
		var obj;
		var n;

		n = String(obj.value);
		o = n.split(".")[0];

		if ( o==null || o.length==0 )
			o = "0";

		//return  o;
		return  obj.value = o;
	}

	/**
	 *	���� ���� �Ҽ��κ��� �����Ͽ� �����κи� ��󳽴�.
	 *	ex1)	getPoint(this,2)
	 *	ex2)	getPoint('1010.22',3)	=>	1010.220
	 *	ex3)	getPoint('1010.22',1)	=>	1010.2
	 */
	function getPoint(obj, floatSize)	{
		var obj;
		var n;
		var floatSize;

		if ( floatSize!=null )
			floatSize = eval(floatSize);
		else
			floatSize = 1;

		n = String(obj.value);
		p = n.split(".")[1];

		if ( p==null || p.length==0 )
			p = "0";

		len = floatSize - p.length;
		if ( len>0 )	{
			for ( k=0;k<len;k++ )
				p=p+"0";
		}
		else	{
			p=p.substring(0,floatSize);
		}

		//return  p;
		return  obj.value = p;
	}

	/**
	 *	���� ���� �Ǽ����·� ������ش�.
	 *	ex1)	formatFloat(this,2)
	 *	ex2)	formatFloat('1001010',2)	=>	1001010.00
	 *	ex3)	formatFloat('100.0',2)		=>	100.00
	 */
	function formatFloat(obj, floatSize)	{
		var obj;
		var n;
		var floatSize;

		if ( floatSize!=null )
			floatSize = eval(floatSize);
		else
			floatSize = 1;

		n = String(obj.value);
		o = n.split(".")[0]
		p = n.split(".")[1]

		if ( o.length==0 )
			o = "0";

		if ( p==null || p.length==0 )
			p = "0";

		len = floatSize - p.length;
		if ( len>0 )	{
			for ( k=0;k<len;k++ )
				p=p+"0";
		}
		else	{
			p=p.substring(0,floatSize);
		}

		//return  o+"."+p;
		return  obj.value=o+"."+p;
	}

	//********************************************
	// FORM object ����
	//********************************************
	/**
	 *	text,passwd,textarea : �޽��� ǥ���� ����
	 */
	function alertFocus(srcObj, strText)	{
		alert(strText);
		srcObj.focus();

		if( (typeof srcObj)=="object"
		&& (srcObj.type=="text" || srcObj.type=="password" || srcObj.type=="textarea") )
			srcObj.select();
	}

	/**
	 *	checkbox control ��� üũ�ϱ�
	 *	document���� form�� ������ �������� 3��° parameter�� form object�� �߰�
	 */
	function checkAll(check_name, mode)	{
		var frmObj = (checkAll.arguments.length==3) ? (checkAll.arguments[2]) : (document.forms[0]);
		for(i=0; i<frmObj.elements.length; ++i)
		{
			if(frmObj.elements[i].name == check_name)
			{
				frmObj.elements[i].checked = mode;
			}
		}
	}

	/**
	 *	checkbox/radio control�� üũ ����
	 *	document���� form�� ������ �������� 2��° parameter�� form object�� �߰�
	 *	radio control�� üũ ���δ� getRadio()�� ���� "" ���ηε� ����
	 */
	function isChecked(check_name)	{
		var frmObj = (isChecked.arguments.length==2) ? (isChecked.arguments[1]) : (document.forms[0]);
		for(i=0; i<frmObj.elements.length; ++i)
		{
			if(frmObj.elements[i].name == check_name)
			{
				if( frmObj.elements[i].checked )
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 *	multi ����� �����Ƿ�...
	 *	function getCheck(check_name){}
	 *	checkbox/radio control�� �� ����
	 *	document���� form�� ������ �������� 3��° parameter�� form object�� �߰�
	 *	radio control�� ������ setRadio�� ��뵵 ����
	 */
	function setCheck(check_name, check_value, mode)	{
		for(i=0; i<check_name.length; ++i)
		{
            if(check_name[i].value == check_value)
            {
                check_name[i].checked = mode;
                return true;
            }
		}
		return false;
	}

	/**
	 *	select control�� ��
	 */
	function getSelect(srcObj)	{
		return srcObj[srcObj.selectedIndex].value;
	}

	/**
	 *	select control�� text
	 */
	function getSelectText(srcObj)	{
		return srcObj[srcObj.selectedIndex].text;
	}

	/**
	 *	select control�� �� ����
	 */
	function setSelect(srcObj, trgValue)	{
		for(var i=0; i<srcObj.options.length; i++)
			if(srcObj.options[i].value == trgValue)
				srcObj.selectedIndex = i;
	}

	/**
	 *	select control�� text ����
	 */
	function setSelectText(srcObj, trgText)	{
		for(var i=0; i<srcObj.options.length; i++)
		{
			if(srcObj.options[i].text == trgText)
			{
				srcObj.selectedIndex = i;
			}
		}
	}

	/**
	 *	radio control�� ��
	 */
	function getRadio(srcObj)	{
		for(var i=0; i<srcObj.length; i++)
		{
			if(srcObj[i].checked)
			{
				return srcObj[i].value;
			}
		}
		return "";
	}

	/**
	 *	radio control�� �� ����
	 */
	function setRadio(srcObj, trgValue)	{
		for(var i=0; i<srcObj.length; i++)
		{
			if(srcObj[i].value == trgValue)
			{
				srcObj[i].click();
			}
		}
	}

	/**
	 *	�Է°����� ���ڿ� ������ �ξ� �˾Ƽ� �����Ѵ�.
	 *
	 *	@param  Object element
	 *	@param  int  MaxLength
	 *	@param  String �޼���
	 *
	 *	ex ) -------------------------------------------
	 *	<textarea name="kkk" onKeyUp="cal_pre(this,100);">
	 *	<input name="kkk" onKeyUp="cal_pre(this,100,'����� ');">
	 */
	function cal_pre(obj,maxLength,eleName)
	{
		var obj;
		var maxLength;
		var eleName;

		return cal_byte(obj,maxLength,eleName);
	}

	/**
	 *	cal_pre���� ���Ǵ� ���� �Լ�
	 */
	function cutText(obj,maxLength,eleName)
	{
		var obj;
		var maxLength;
		var eleName;

		var tmpStr;
		var temp=0;
		var onechar;
		var tcount;
		tcount = 0;

		tmpStr = new String(obj.value);
		temp = tmpStr.length;

		for(k=0;k<temp;k++)
		{
			onechar = tmpStr.charAt(k);

			if(escape(onechar).length > 4) {
				tcount += 2;
			}
			else {
				tcount++;
			}

			if(tcount>eval(maxLength)) {
				tmpStr = tmpStr.substring(0,k);
				break;
			}
		}
		obj.value = tmpStr;
		cal_byte(obj,maxLength,eleName);
	}

	function cut_string(str,maxLength)
	{
		var tmpStr = new String(str);
		var temp = tmpStr.length;
		var onechar;
		var tcount = 0;

		for(var k=0; k < temp ; k++) 	{
			onechar = tmpStr.charAt(k);
			if(escape(onechar).length > 4) {
				tcount += 2;
			} else {
				tcount++;
			}

			if( tcount > eval(maxLength)) {
				tmpStr = tmpStr.substring(0,k);
				break;
			}
		}
		
		if (str.length > tmpStr.length) {
			return tmpStr + '...';
		}

		return tmpStr;
	}

	/**
	 *	cal_pre���� ���Ǵ� ���� �Լ�
	 */
	function cal_byte(obj,maxLength,eleName)
	{
		var obj
		var maxLength;
		var eleName;

		var tmpStr;
		var temp=0;
		var onechar;
		var tcount;
		tcount = 0;

		tmpStr = new String(obj.value);
		temp = tmpStr.length;

		for (k=0;k<temp;k++)
		{
			onechar = tmpStr.charAt(k);

			if (escape(onechar).length > 4) {
				tcount += 2;
			}
			else {
				tcount++;
			}
		}

		if(tcount>eval(maxLength)) {

			reserve = tcount-eval(maxLength);
			if(eleName == null)
				eleName = "";
			if ( !isEng )	{
				alert(eleName + "�ִ� "+maxLength+" Byte ��밡���մϴ�.\r\n���� "+reserve+" Byte�� �Ѿ����ϴ�.");
			}	else	{
				alert(eleName + "�ִ� "+maxLength+" Byte ��밡���մϴ�.\r\n���� "+reserve+" Byte�� �Ѿ����ϴ�.");
			}
			cutText(obj,maxLength,eleName);
			return false;
		}
	}

	// �����˾�
	function comPopup(formObj,inAction,inScroll,LeftPosition,TopPosition,inWidth,inHeight) {

		var f = eval(formObj);
		
		f.target = "zonekom";
		f.action = inAction; 
		f.method = "post";

		zonekom = window.open("", "zonekom" , "scrollbars=" + inScroll + ", left="+LeftPosition+" ,top="+TopPosition+" ,width=" + inWidth + ", height=" + inHeight);
		f.submit();
	}

	function openWindow(target, w, h, name) {
		x = (screen.availWidth - w) / 2;
		y = (screen.availHeight - h) / 2;
		var win = window.open(target, name, 'scrollbars,width=' + w + ',height=' + h + ',screenX=' + x + ',screenY=' + y + ',left=' + x + ',top=' + y);
		win.focus();
	}

	// ZONEKOM POPUP.
	// 1st arg	: action
	// 2nd Arg	: width
	// 3rd Arg	: height
	// 4,5,6,7...	: Request Values.
	function ZKWindow() {
        var i, numArgs = arguments.length;

        if (numArgs < 1) {
            return false;
        }

        var oInput  = new Array();
        var oForm = document.createElement("FORM");
        oForm.name = "frmZKRequest";
        oForm.method = "POST";
        oForm.action = arguments[0];

        for (i=3; i < numArgs; i++) {
            oInput[i] = document.createElement("INPUT");
            oInput[i].type = "hidden";
            oInput[i].name = "req_"+ (i - 2);
            oInput[i].value = arguments[3];
            oForm.appendChild(oInput[i]);
        }

        document.all.tags("BODY").item(0).appendChild(oForm);

        var dispY, dispY;
        dispX = (document.all.tags("BODY").item(0).offsetWidth - arguments[1]) / 2;
        dispY = (document.all.tags("BODY").item(0).offsetHeight - arguments[2]) / 2;

        comPopup(oForm, arguments[0], "n", dispX, dispY, arguments[1], arguments[2]);
    }

	// ��ü����
	function allcheck(obj, chkNm){
		for( var i=0; i < obj.elements.length; i++) 
		{
			var e = obj.elements[i];
			if( e.name == chkNm && e.disabled != true )
				e.checked = true;
		}
		return;
	}

	// �������
	function allcheck_false(obj, chkNm){
	
		for( var i=0; i < obj.elements.length; i++) 
		{
			var e = obj.elements[i];
			if( e.name == chkNm )
				e.checked = false;
		}
		return;
	}

	function onlyNum(){
	   if((event.keyCode<48)||(event.keyCode>57))
		  event.returnValue=false;
	}

	//	����Ʈ���� ������ obj�� �޾Ƽ� ���° line�� �ִ����� return�Ѵ�.
	function getCurLine(obj) {
		var f = obj.form;
		var n = obj.name;
		var j = -1;
		var inx;

		for ( i=0; i<f.length; i++ ) {
			if ( f.elements[i].name == n ) {
				j++;
				if ( f.elements[i] == obj )	{
					elementName = f.elements[i].name;
					inx = j;
					//alert(f.elements[i].name + " : " + j);
					return inx;
				}
			}
		}
	}

	//	����Ʈ���� ������ obj�� �޾Ƽ� ���� �Ѷ��μ� �˾ƿ���
	function getCurTotalLine(obj) {
		var f = obj.form;
		var n = obj.name;
		var j = -1;
		var inx;
		var tinx = 0;

		for ( i=0; i<f.length; i++ ) {
			if ( f.elements[i].name == n ) {
				elementName = f.elements[i].name;
				if (f.elements[i].name == "p_index"+[i]); {
					tinx ++;
				}
			}
		}
		return tinx;
	}


	// key Press check(key Event ���ڰ˻�) + dot
	function dot_digitCheckEvent()
	{
		if(((event.keyCode != 46) && (event.keyCode<48)) || (event.keyCode>57))
			event.returnValue=false;
	}

	// Input value digit check + dot
	// return vlaue : if error -1 else if src src is digit 
	function dot_digitCheck(src) 
	{
		for(var i=0; i < src.length; i++)
		{
			if(((src.substring(i,i+1)).indexOf(".") == -1) && (src.substring(i,i+1) < "0" || src.substring(i,i+1) > "9"))
			{
				return -1;
			}
		}
		return src;
	}
	
	/*
	 *	�ڵ� ��Ŀ�� �̵�
	 *  ex) onkeyup="js_next_focus(this,6,'focus next name')"
	 */
	function js_next_focus(obj,sLength,sNext)
	{
		if (obj.value.length == sLength)
		{
			document.getElementsByName(sNext)[0].focus()
		}
	}

	/*
		2004-10-12
		function CheckLenkki(field, maxlimit, format)
		field ==> �Է°�
		maxlimit ==>��� bytes
		format ==> �Է��ϴ� ���� �̸�
		���� �Է°� byteȯ��� Ư������ ���� �ִ� ���� ����
	*/
	function CheckLenkki(field, maxlimit, format) {
		var temp;
		var strbyte;
		strbyte = 0;
		len = field.value.length;

		for(k=0;k<len;k++) {
			temp = field.value.charAt(k);

			if(escape(temp).length > 4) {
				strbyte += 2;
			} else {
				if (temp=="<" || temp==">") {
					strbyte += 4;
				}else if (temp=="\""){
					strbyte += 6;
				}else{
					strbyte++;
				}
			}
			if (strbyte > maxlimit){
				//alert(format + "��(��) " + maxlimit + " Byte�� ������ �����ϴ�.");	--	( byte ��� �ѱ� XX�ڷ� ���� : jiyun )
				alert(format + "��(��) �ѱ�" + (maxlimit/2) + "�� �� ������ �����ϴ�.");
				field.value = field.value.substring(0, k);
				break;
			}

		}
	}

	// ���͸���
	function udf_keydown() {
		if ( event.keyCode == 13 ) {
			 event.returnValue=false;
		}
	}

		
	//----------------------------------------------------------------------------
	// Ư������ X�� Y�� ��� �ٲ�.
	//----------------------------------------------------------------------------
	function replaceall (str,x,y) {

	  var dest =  "";
	  var c;
	  
	  for (var i=0; i<str.length; i++) {
		if (str.substring(i,i+1) == x)
			c = y;
		else
			c = str.substring(i,i+1)
		dest = dest + c;
	  }
	  return dest
	}
// ====================================== tambahan ========================================	
	
	
/**
 * Input Validation(check existance input)
 * if find input empty or just fill with " ", call trim function above and return false
 *
 * @param string input
 * @param string types
 * 
 * @return string
 */
function filterNum(str)
{
	re=/^\$|,|\s+/g;
	return str.replace(re,"");
}	
	
/**
 * Input Validation(check existance input)
 * if find input empty or just fill with " ", call trim function above and return false
 *
 * @param string input
 * @param string types
 * 
 * @return string
 */
function commaSplitAndNumberOnly(ob)
{
	var txtNumber='' + ob.value;
	
	while (txtNumber.length > 0 && (isNaN(txtNumber) || txtNumber.indexOf('.') != -1 )) { 
		txtNumber = txtNumber.substring(0, txtNumber.length - 1);
	}

	var rxSplit   = new RegExp('([0-9])([0-9][0-9][0-9][,.])');
	var arrNumber = txtNumber.split('.');
	arrNumber[0] +='.';
	
	do {
			arrNumber[0] = arrNumber[0].replace(rxSplit,'$1,$2');
	} while(rxSplit.test(arrNumber[0]));
	
	if(arrNumber.length > 1) {
		txtNumber = arrNumber.join('');
	}
	else{
		txtNumber = arrNumber[0].split('.')[0];
	}

	ob.focus();
	return txtNumber;
}

//alert(isNaN('1,234.00'));
/**
 * Input Validation(check existance input)
 * if find input empty or just fill with " ", call trim function above and return false
 *
 * @param string input
 * @param string types
 * 
 * @return string
 */
function commaSplitAndAllowDot(ob)
{
	var txtNumber='' + ob.value;
	
	while ( txtNumber.length > 0 && isNaN(txtNumber) ) {
		txtNumber = txtNumber.substring(0, txtNumber.length - 1 );
	}
	
	ob.value = txtNumber;
	
	var rxSplit   = new RegExp('([0-9])([0-9][0-9][0-9][,.])');
	var arrNumber = txtNumber.split('.');
	arrNumber[0] += '.';
	
	do {
		arrNumber[0] = arrNumber[0].replace(rxSplit,'$1,$2');
	} while (rxSplit.test(arrNumber[0]));
	
	if (arrNumber.length > 1) {
		txtNumber = arrNumber.join('');
	} else {
		txtNumber = arrNumber[0].split('.')[0];
	}
	
	ob.focus();
	return txtNumber;
}


/**
 * Input Validation(check existance input)
 * if find input empty or just fill with " ", call trim function above and return false
 *
 * @param string input
 * @param string types
 * 
 * @return string
 */
function noSplitAndNumberOnly(ob) 
{
    var txtNumber=''+ob.value;
    while ( txtNumber.length > 0 && (isNaN(txtNumber) || txtNumber.indexOf('.')!=-1 )){
        txtNumber = txtNumber.substring(0, txtNumber.length - 1 );
    }	
	
	ob.value = txtNumber;
	ob.focus();
	return ob.value;	
}

/**
 * Input Validation(check number)
 * call function filterNum and commaSplitAndNumberOnly
 *
 * 
 * @return string
 */
function formatNumber(ob, type)
{
//    var ob=event.srcElement;

    ob.value = filterNum(ob.value);
	if(type =="comma") {
	    ob.value = commaSplitAndNumberOnly(ob);
	} else if(type=="dot") {
	    ob.value = commaSplitAndAllowDot(ob);
	} else {
	    ob.value = noSplitAndNumberOnly(ob);
	}
    return false;
}


/**
 * Input Validation(check blank--->" " input)
 * if find blank-->" " input, replace with "" (same like trim-built-in function)
 *
 * @param string
 * 
 * @return string
 */
function trim(string)
{
	return string.replace(/^\s+|\s+$/g,'');
}


/**
 * Input Validation(check blank)
 * If input are blank--->" ", it will return false.
 *
 * @param string form name
 * @param string field name
 * 
 * @return boolean
 */
function validRequired(formField,fieldLabel)
{
	var result = true;
	var str = trim(formField.value);
	
	if (str == ""){
		alert(fieldLabel);
		if (formField.type != 'hidden') {
			formField.focus();
		}
		
		result = false;
	}
	
	// make sure the value contain no extra spaces
	try{  formField.value = str;} catch (e) {}
	return result;
}

/**
 * Email Validation
 * If the email address is valid return true.
 *
 * @param string email address
 * 
 * @return boolean
 */
function isEmailAddr(email)
{
	var regex = /^[-_.a-z0-9]+@(([-a-z0-9]+\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i;
	return regex.test(email);
}

/**
 * Email Validation
 * If the email address is valid return true.
 *
 * @param string email address
 * 
 * @return boolean
 */
function validEmail(formField,fieldLabel,required)
{
  var result = true;
  
  if (required && !validRequired(formField,fieldLabel))
    result = false;

  if (result && ((formField.value.length < 3) || !isEmailAddr(formField.value))){
    alert("�̸��� �ּҸ� �ùٸ��� �Է��� �ּ���(yourname@yourdomain.com)");
    formField.focus();
    result = false;
  }

   return result;
}

/**
 * Input Validation(validate length for password and userid)
 * Check input userid range(6-10) and password(4-10)
 *
 * @param string field name
 * @param string field type(password or userid)
 * 
 * @return boolean
 */
function validLength(formField)
{
	var result=true;

  if(formField.type=="password"){
	if(formField.value.length<3||formField.value.length>10){
		alert('��ȣ�� ���̴� 3-10�� ���̷� ����ϼž� �˴ϴ�.');
		formField.focus();
		result = false;
	}
  
	return result;
  } else {
    
	if(formField.value.length<5||formField.value.length>10){
	  alert('���̵��� ���̴� 5-10�� ���̷� ����ϼž� �˴ϴ�.');
      formField.focus();
      result = false;
    }
		return result;
  }
}

/**
 * Input Validation(validate length for password and userid)
 * Check input userid range(6-10) and password(4-10)
 *
 * @param string field name
 * @param string field type(password or userid)
 * 
 * @return boolean
 */
function validLength(formField)
{
	var result=true;

  if(formField.type=="password"){
	if(formField.value.length<3||formField.value.length>10){
		alert('��ȣ�� ���̴� 3-10�� ���̷� ����ϼž� �˴ϴ�.');
		formField.focus();
		result = false;
	}
  
	return result;
  } else {
    
	if(formField.value.length<5||formField.value.length>10){
	  alert('���̵��� ���̴� 5-10�� ���̷� ����ϼž� �˴ϴ�.');
      formField.focus();
      result = false;
    }
		return result;
  }
}

/**
 * Input Validation(validate for password and confirm password)
 * If password and confirmPassword are same, return true
 *
 * @param string field name(password)
 * @param string field name to compared(confirm password)
 * 
 * @return boolean
 */
function validSamePass(formField,comparedField)
{
  var result = true;
  
  if(formField.value!=comparedField.value){
    alert('�Է��Ͻ� ��ȣ�� ��ġ���� �ʽ��ϴ�.');
    comparedField.focus();
    result = false;
  }

  return result;
}

// check period
function validPeriod(d1, d2) {

	var d_1 = parseDate(d1.value, 'prefer_euro_format');
	var d_2 = parseDate(d2.value, 'prefer_euro_format');
	
	if(d_1 == null || d_2 == null) {
		alert("Please input correct date");
		d1.value = '';
		d2.value = '';
		d1.focus();
		return false;
	} else if (d_1.getTime() > d_2.getTime()) {
		alert("TO date is more earlier than FROM date");
		d1.value = '';
		d2.value = '';
		d1.focus();
		return false;
	} else {
		d1.value = formatDate(d_1, 'dd-NNN-yyyy');
		d2.value = formatDate(d_2, 'dd-NNN-yyyy');
		return true;
	}
}

// check date
function validDate(d) {
	var pDate = parseDate(d.value, 'prefer_euro_format');

	if(pDate == null) {
		alert("Please input date with proper date format");
		d.value = "";
		d.focus();
		return false;
	} else {
		d.value = formatDate(pDate, 'dd-NNN-yyyy');
		return pDate;
	}
}


function setPeriod (d_ts, period, obj1, obj2) {
	var d = new Date(d_ts);
	var lastDate = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var date_from;
	var date_to;

	switch (period) {
		case "thisWeek" :
			date_from = new Date(d_ts - (d.getDay()*86400000));
			date_to	= new Date(d_ts + ((6 - d.getDay())*86400000));
			break;
		
		case "lastWeek" :
			date_from = new Date(d_ts - (d.getDay()*86400000) - 86400000*7);
			date_to	= new Date(d_ts + ((6 - d.getDay())*86400000) - 86400000*7);
			break;
		
		case "nextWeek" :
			date_from = new Date(d_ts - (d.getDay()*86400000) + 86400000*7);
			date_to	= new Date(d_ts + ((6 - d.getDay())*86400000) + 86400000*7);
			break;
		
		case "thisMonth" : 
			date_from = new Date(d.getFullYear(), d.getMonth(), 1);
			date_to = new Date(d.getFullYear(), d.getMonth(), lastDate[d.getMonth()]);
			break;

		case "lastMonth" :
			if(d.getMonth() == 0) { // if january
				date_from = new Date(d.getFullYear() - 1, 11, 1);
				date_to = new Date(d.getFullYear() - 1, 11, 31);
			} else {
				date_from = new Date(d.getFullYear(), d.getMonth() - 1, 1);
				date_to = new Date(d.getFullYear(), d.getMonth() - 1, lastDate[d.getMonth()-1]);
			}
			break;

		case "nextMonth" :
			if(d.getMonth() == 11) { // if december
				date_from = new Date(d.getFullYear() + 1, 0, 1);
				date_to = new Date(d.getFullYear() + 1, 0, 31);
			} else {
				date_from = new Date(d.getFullYear(), d.getMonth() + 1, 1);
				date_to = new Date(d.getFullYear(), d.getMonth() + 1, lastDate[d.getMonth()+1]);
			}
			break;
	}
	
	obj1.value = formatDate(date_from, 'dd-NNN-yyyy');
	obj2.value = formatDate(date_to, 'dd-NNN-yyyy');
}

// SET DATE
// parameter
//d_ts : the day's timestamp
//curr : 0 the day, 1 theday +1, -1, the day -1....
//onj : target object
function setDate(d_ts, curr, obj) {
	if(curr != '') {
		var d = new Date(d_ts + (parseInt(curr) * 86400000)); // 86400000 is 1 day
		obj.value = formatDate(d, 'dd-NNN-yyyy');
	} else {
		obj.value = "";
	}
}

function setFilterDateCalc(d, val, obj) {
	if(d == null) {
		d = new Date(); val = 0;
	}
	d.setDate(d.getDate()+val);
	obj.value = formatDate(d, 'd-NNN-yyyy');

}

function setFilterPeriodCalc(d, val, obj1, obj2) {
	var lastDate = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var date_from = parseDate(obj1.value, 'prefer_euro_format');
	var date_to = parseDate(obj2.value, 'prefer_euro_format');
	if(date_from == null && date_to == null) {
		date_from = new Date(d.getFullYear(), d.getMonth(), 1);
		date_to = new Date(d.getFullYear(), d.getMonth(), lastDate[d.getMonth()]);
		value = 0;
	} else {
		d = new Date(parseDate(obj1.value, 'prefer_euro_format'));
		if(d.getMonth() == 0 && val < 0) {
			date_from = new Date(d.getFullYear() - 1, 11, 1);
			date_to = new Date(d.getFullYear() - 1, 11, 31);
		} else if(d.getMonth() == 11 && val > 0) {
			date_from = new Date(d.getFullYear() + 1, 0, 1);
			date_to = new Date(d.getFullYear() + 1, 0, 31);
		} else {
			m = d.getMonth() + parseInt(val);
			date_from = new Date(d.getFullYear(), m, 1);
			if(m == 1)
			{
			  if (d.getFullYear() % 4 == 0)
			  {
			    date_to = new Date(d.getFullYear(), m, 29);
			  }
			  else
			  {
			    date_to = new Date(d.getFullYear(), m, lastDate[m]);
			  }
			}
			else
			{
			  date_to = new Date(d.getFullYear(), m, lastDate[m]);
			}
		}
	}
	obj1.value = formatDate(date_from, 'dd-NNN-yyyy');
	obj2.value = formatDate(date_to, 'dd-NNN-yyyy');
}


function in_array(what, where){
	var a=false;
	for(var i=0;i<where.length;i++){
	  if(what == where[i]){
	    a=true;
        break;
	  }
	}
	return a;
}


var orig_color;
function highlighter(sw, o, color) {
	if(sw) {
		orig_color = o.style.backgroundColor;
		o.style.backgroundColor = color;
	} else {
		o.style.backgroundColor = orig_color;
	}
}

function addOption(selectbox,text,value ) {
	var optn = document.createElement("OPTION");
	optn.text = text;
	optn.value = value;
	selectbox.options.add(optn);
}

var ip = "192.168.1.88";