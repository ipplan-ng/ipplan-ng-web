/**
 * Object PHP_Serializer
 * 	JavaScript to PHP serialize / unserialize class.
 * This class converts php variables to javascript and vice versa.
 *
 * PARSABLE JAVASCRIPT < === > PHP VARIABLES:
 *	[ JAVASCRIPT TYPE ]		[ PHP TYPE ]
 *	Array		< === > 	array
 *	Object		< === > 	class (*)
 *	String		< === > 	string
 *	Boolean		< === > 	boolean
 *	null		< === > 	null
 *	Number		< === > 	int or double
 *	Date		< === > 	class
 *	Error		< === > 	class
 *	Function	< === > 	class (*)
 *
 * (*) NOTE:
 * Any PHP serialized class requires the native PHP class to be used, then it's not a
 * PHP => JavaScript converter, it's just a usefull serilizer class for each
 * compatible JS and PHP variable types.
 * Lambda, Resources or other dedicated PHP variables are not usefull for JavaScript.
 * There are same restrictions for javascript functions*** too then these will not be sent.
 *
 * *** function test(); alert(php.serialize(test)); will be empty string but
 * *** mytest = new test(); will be sent as test class to php
 * _____________________________________________
 *
 * EXAMPLE:
 *	var php = new PHP_Serializer(); // use new PHP_Serializer(true); to enable UTF8 compatibility
 *	alert(php.unserialize(php.serialize(somevar)));
 *	// should alert the original value of somevar
 * ---------------------------------------------
 * @author              Andrea Giammarchi
 * @site		www.devpro.it
 * @date                2005/11/26
 * @lastmod             2006/05/15 19:00 [modified stringBytes method and removed replace for UTF8 and \r\n]
 * 			[add UTF8 var again, PHP strings if are not encoded with utf8_encode aren't compatible with this object]
 *			[Partially rewrote for a better stability and compatibility with Safari or KDE based browsers]
 *			[UTF-8 now has a native support, strings are converted automatically with ISO or UTF-8 charset]
 *
 * @specialthanks	Fabio Sutto, Kentaromiura, Kroc Camen, Cecile Maigrot, John C.Scott, Matteo Galli
 *
 * @version             2.2, tested on FF 1.0, 1.5, IE 5, 5.5, 6, 7 beta 2, Opera 8.5, Konqueror 3.5, Safari 2.0.3
 */
function PHP_Serializer(UTF8) {
	
	/** public methods */
	function serialize(v) {
		// returns serialized var
		var	s;
		switch(v) {
			case null:
				s = "N;";
				break;
			default:
				s = this[this.__sc2s(v)] ? this[this.__sc2s(v)](v) : this[this.__sc2s(__o)](v);
				break;
		};
		return s;
	};
	
	function unserialize(s) {
		// returns unserialized var from a php serialized string
		__c = 0;
		__s = s;
		return this[__s.substr(__c, 1)]();
	};
	
	function stringBytes(s) {
		// returns the php lenght of a string (chars, not bytes)
		return s.length;
	};
	
	function stringBytesUTF8(s) {
		// returns the php lenght of a string (bytes, not chars)
		var 	c, b = 0,
			l = s.length;
		while(l) {
			c = s.charCodeAt(--l);
			b += (c < 128) ? 1 : ((c < 2048) ? 2 : ((c < 65536) ? 3 : 4));
		};
		return b;
	};
	
	/** private methods */
	function __sc2s(v) {
		return v.constructor.toString();
	};
	
	function __sc2sKonqueror(v) {
		var	f;
		switch(typeof(v)) {
			case ("string" || v instanceof String):
				f = "__sString";
				break;
			case ("number" || v instanceof Number):
				f = "__sNumber";
				break;
			case ("boolean" || v instanceof Boolean):
				f = "__sBoolean";
				break;
			case ("function" || v instanceof Function):
				f = "__sFunction";
				break;
			default:
				f = (v instanceof Array) ? "__sArray" : "__sObject";
				break;
		};
		return f;
	};
	
	function __sNConstructor(c) {
		return (c === "[function]" || c === "(Internal Function)");
	};
	
	function __sCommonAO(v) {
		var	b, n,
			a = 0,
			s = [];
		for(b in v) {
			n = v[b] == null;
			if(n || v[b].constructor != Function) {
				s[a] = [
					(!isNaN(b) && parseInt(b).toString() === b ? this.__sNumber(b) : this.__sString(b)),
					(n ? "N;" : this[this.__sc2s(v[b])] ? this[this.__sc2s(v[b])](v[b]) : this[this.__sc2s(__o)](v[b]))
				].join("");
				++a;
			};
		};
		return [a, s.join("")];
	};
	
	function __sBoolean(v) {
		return ["b:", (v ? "1" : "0"), ";"].join("");
	};
	
	function __sNumber(v) {
		var 	s = v.toString();
		return (s.indexOf(".") < 0 ? ["i:", s, ";"] : ["d:", s, ";"]).join("");
	};
	
	function __sString(v) {
		return ["s:", v.length, ":\"", v, "\";"].join("");
	};
	
	function __sStringUTF8(v) {
		return ["s:", this.stringBytes(v), ":\"", v, "\";"].join("");
	};
	
	function __sArray(v) {
		var 	s = this.__sCommonAO(v);
		return ["a:", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sObject(v) {
		var 	o = this.__sc2s(v),
			n = o.substr(__n, (o.indexOf("(") - __n)),
			s = this.__sCommonAO(v);
		return ["O:", this.stringBytes(n), ":\"", n, "\":", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sObjectIE7(v) {
		var 	o = this.__sc2s(v),
			n = o.substr(__n, (o.indexOf("(") - __n)),
			s = this.__sCommonAO(v);
		if(n.charAt(0) === " ")
			n = n.substring(1);
		return ["O:", this.stringBytes(n), ":\"", n, "\":", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sObjectKonqueror(v) {
		var	o = v.constructor.toString(),
			n = this.__sNConstructor(o) ? "Object" : o.substr(__n, (o.indexOf("(") - __n)),
			s = this.__sCommonAO(v);
		return ["O:", this.stringBytes(n), ":\"", n, "\":", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sFunction(v) {
		return "";
	};
	
	function __uCommonAO(tmp) {
		var	a, k;
		++__c;
		a = __s.indexOf(":", ++__c);
		k = parseInt(__s.substr(__c, (a - __c))) + 1;
		__c = a + 2;
		while(--k)
			tmp[this[__s.substr(__c, 1)]()] = this[__s.substr(__c, 1)]();
		return tmp;
	};

	function __uBoolean() {
		var	b = __s.substr((__c + 2), 1) === "1" ? true : false;
		__c += 4;
		return b;
	};
	
	function __uNumber() {
		var	sli = __s.indexOf(";", (__c + 1)) - 2,
			n = Number(__s.substr((__c + 2), (sli - __c)));
		__c = sli + 3;
		return n;
	};
	
	function __uStringUTF8() {
		var 	c, sls, sli, vls,
			pos = 0;
		__c += 2;
		sls = __s.substr(__c, (__s.indexOf(":", __c) - __c));
		sli = parseInt(sls);
		vls = sls = __c + sls.length + 2;
		while(sli) {
			c = __s.charCodeAt(vls);
			pos += (c < 128) ? 1 : ((c < 2048) ? 2 : ((c < 65536) ? 3 : 4));
			++vls;
			if(pos === sli)
				sli = 0;
		};
		pos = (vls - sls);
		__c = sls + pos + 2;
		return __s.substr(sls, pos);
	};
	
	function __uString() {
		var 	sls, sli;
		__c += 2;
		sls = __s.substr(__c, (__s.indexOf(":", __c) - __c));
		sli = parseInt(sls);
		sls = __c + sls.length + 2;
		__c = sls + sli + 2;
		return __s.substr(sls, sli);
	};
	
	function __uArray() {
		var	a = this.__uCommonAO([]);
		++__c;
		return a;
	};
	
	function __uObject() {
		var 	tmp = ["s", __s.substr(++__c, (__s.indexOf(":", (__c + 3)) - __c))].join(""),
			a = tmp.indexOf("\""),
			l = tmp.length - 2,
			o = tmp.substr((a + 1), (l - a));
		if(eval(["typeof(", o, ") === 'undefined'"].join("")))
			eval(["function ", o, "(){};"].join(""));
		__c += l;
		eval(["tmp = this.__uCommonAO(new ", o, "());"].join(""));
		++__c;
		return tmp;
	};
	
	function __uNull() {
		__c += 2;
		return null;
	};
	
	function __constructorCutLength() {
		function ie7bugCheck(){};
		var	o1 = new ie7bugCheck(),
			o2 = new Object(),
			c1 = __sc2s(o1),
			c2 = __sc2s(o2);
		if(c1.charAt(0) !== c2.charAt(0))
			__ie7 = true;
		return (__ie7 || c2.indexOf("(") !== 16) ? 9 : 10;
	};
	
	/** private variables */
	var 	__c = 0,
		__ie7 = false,
		__b = __sNConstructor(__c.constructor.toString()),
		__n = __b ? 9 : __constructorCutLength(),
		__s = "",
		__a = [],
		__o = {},
		__f = function(){};
	
	/** public prototypes */
	PHP_Serializer.prototype.serialize = serialize;
	PHP_Serializer.prototype.unserialize = unserialize;
	PHP_Serializer.prototype.stringBytes = UTF8 ? stringBytesUTF8 : stringBytes;
	
	/** serialize: private prototypes */
	if(__b) { // Konqueror / Safari prototypes
		PHP_Serializer.prototype.__sc2s = __sc2sKonqueror;
		PHP_Serializer.prototype.__sNConstructor = __sNConstructor;
		PHP_Serializer.prototype.__sCommonAO = __sCommonAO;
		PHP_Serializer.prototype[__sc2sKonqueror(__b)] = __sBoolean;
		PHP_Serializer.prototype.__sNumber = 
		PHP_Serializer.prototype[__sc2sKonqueror(__n)] = __sNumber;
		PHP_Serializer.prototype.__sString = PHP_Serializer.prototype[__sc2sKonqueror(__s)] = UTF8 ? __sStringUTF8 : __sString;
		PHP_Serializer.prototype[__sc2sKonqueror(__a)] = __sArray;
		PHP_Serializer.prototype[__sc2sKonqueror(__o)] = __sObjectKonqueror;
		PHP_Serializer.prototype[__sc2sKonqueror(__f)] = __sFunction;
	}
	else { // FireFox, IE, Opera prototypes
		PHP_Serializer.prototype.__sc2s = __sc2s;
		PHP_Serializer.prototype.__sCommonAO = __sCommonAO;
		PHP_Serializer.prototype[__sc2s(__b)] = __sBoolean;
		PHP_Serializer.prototype.__sNumber = 
		PHP_Serializer.prototype[__sc2s(__n)] = __sNumber;
		PHP_Serializer.prototype.__sString = PHP_Serializer.prototype[__sc2s(__s)] = UTF8 ? __sStringUTF8 : __sString;
		PHP_Serializer.prototype[__sc2s(__a)] = __sArray;
		PHP_Serializer.prototype[__sc2s(__o)] = __ie7 ? __sObjectIE7 : __sObject;
		PHP_Serializer.prototype[__sc2s(__f)] = __sFunction;
	};
	
	/** unserialize: private prototypes */
	PHP_Serializer.prototype.__uCommonAO = __uCommonAO;
	PHP_Serializer.prototype.b = __uBoolean;
	PHP_Serializer.prototype.i =
	PHP_Serializer.prototype.d = __uNumber;
	PHP_Serializer.prototype.s = UTF8 ? __uStringUTF8 : __uString;
	PHP_Serializer.prototype.a = __uArray;
	PHP_Serializer.prototype.O = __uObject;
	PHP_Serializer.prototype.N = __uNull;
};