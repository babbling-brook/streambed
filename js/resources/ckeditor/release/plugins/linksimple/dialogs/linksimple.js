﻿/*
 Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function(){CKEDITOR.dialog.add("linksimpleDialog",function(c){return{title:"Link",minWidth:400,minHeight:200,contents:[{id:"tab-basic",label:"Enter link details",elements:[{type:"text",id:"linktext",label:"Link text",validate:CKEDITOR.dialog.validate.notEmpty("Link text field cannot be empty."),setup:function(a){this.setValue(a.getText())},commit:function(a){a.setText(this.getValue())}},{type:"text",id:"url",label:"URL",validate:CKEDITOR.dialog.validate.notEmpty("URL field cannot be empty."),setup:function(a){this.setValue(a.getAttribute("href"))},
commit:function(a){var b=this.getValue();"http://"!==b.substr(0,7)&&("https://"!==b.substr(0,8)&&"ftp://"!==b.substr(0,7))&&(b="http://"+b);a.setAttribute("href",b)}}]}],onShow:function(){var a=c.getSelection(),b=a.getStartElement();b&&(b=b.getAscendant("a",!0));!b||"a"!==b.getName()?(a=a.getSelectedText(),b=c.document.createElement("a"),b.setText(a),this.insertMode=!0):this.insertMode=!1;this.element=b;this.setupContent(this.element)},onOk:function(){var a=this.element;this.commitContent(a);this.insertMode&&
c.insertElement(a)}}})})();