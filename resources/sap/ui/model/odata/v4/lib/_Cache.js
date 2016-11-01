/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2016 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["jquery.sap.global","./_Helper"],function(q,_){"use strict";var C;function c(Q,R,D){Object.keys(Q).forEach(function(k){var v=Q[k];if(D&&k[0]==='$'){return;}switch(k){case"$expand":v=C.convertExpand(v);break;case"$filter":case"$orderby":break;case"$select":if(Array.isArray(v)){v=v.join(",");}break;default:if(k[0]==='$'){throw new Error("Unsupported system query option "+k);}}R(k,v);});}function d(R,p,l){if(p){p.split("/").every(function(s){if(!R||typeof R!=="object"){l(s);R=undefined;return false;}R=R[s];if(R===undefined){l(s);return false;}return true;});}return R;}function f(A,v,s,e){var i;for(i=s;i<e;i++){A[i]=v;}}function r(o,s,e,g){var E=o.aElements,b=e-s,p,R=o.sResourcePath+"$skip="+s+"&$top="+b;p=o.oRequestor.request("GET",R,g).then(function(h){var i,j=h.value.length,k;if(E!==o.aElements){k=new Error("Refresh canceled pending request: "+o.oRequestor.getServiceUrl()+R);k.canceled=true;throw k;}o.sContext=h["@odata.context"];if(j<b){o.iMaxElements=s+j;o.aElements.splice(o.iMaxElements,b-j);}for(i=0;i<j;i++){o.aElements[s+i]=h.value[i];}})["catch"](function(h){if(E===o.aElements){f(o.aElements,undefined,s,e);}throw h;});f(o.aElements,p,s,e);}function a(R,s,Q){var b=C.buildQueryString(Q);this.sContext=undefined;this.aElements=[];this.iMaxElements=-1;this.mQueryOptions=Q;this.oRequestor=R;this.sResourcePath=s+b+(b.length?"&":"?");}a.prototype.read=function(I,l,g,p,D){var i,e=I+l,G=-1,b=false,t=this;if(I<0){throw new Error("Illegal index "+I+", must be >= 0");}if(l<0){throw new Error("Illegal length "+l+", must be >= 0");}else if(l!==1&&p!=undefined){throw new Error("Cannot drill-down for length "+l);}if(this.iMaxElements>=0&&e>this.iMaxElements){e=this.iMaxElements;}for(i=I;i<e;i++){if(this.aElements[i]!==undefined){if(G>=0){r(this,G,i,g);b=true;G=-1;}}else if(G<0){G=i;}}if(G>=0){r(this,G,e,g);b=true;}if(b&&D){D();}return Promise.all(this.aElements.slice(I,e)).then(function(){var R;if(p!=undefined){R=t.aElements[I];return d(R,p,function(s){q.sap.log.error("Failed to drill-down into "+t.sResourcePath+"$skip="+I+"&$top=1 via "+p+", invalid segment: "+s,null,"sap.ui.model.odata.v4.lib._Cache");});}return{"@odata.context":t.sContext,value:t.aElements.slice(I,e)};});};a.prototype.refresh=function(){this.sContext=undefined;this.iMaxElements=-1;this.aElements=[];};a.prototype.toString=function(){return this.oRequestor.getServiceUrl()+this.sResourcePath;};a.prototype.update=function(g,p,v,e,P){var b={},h,R=d(this.aElements,P);e+=C.buildQueryString(this.mQueryOptions,true);h={"If-Match":R["@odata.etag"]};b[p]=R[p]=v;return this.oRequestor.request("PATCH",e,g,h,b).then(function(o){for(p in R){if(p in o){R[p]=o[p];}}return o;});};function S(R,s,Q,b,p){this.bPost=p;this.bPosting=false;this.oPromise=null;this.mQueryOptions=Q;this.oRequestor=R;this.sResourcePath=s+C.buildQueryString(Q);this.bSingleProperty=b;}S.prototype.post=function(g,D){var t=this;if(!this.bPost){throw new Error("POST request not allowed");}if(this.bPosting){throw new Error("Parallel POST requests not allowed");}this.oPromise=this.oRequestor.request("POST",this.sResourcePath,g,undefined,D).then(function(R){t.bPosting=false;return R;},function(e){t.bPosting=false;throw e;});this.bPosting=true;return this.oPromise;};S.prototype.read=function(g,p,D){var t=this,P,R=this.sResourcePath;if(!this.oPromise){if(this.bPost){throw new Error("Read before a POST request");}P=this.oRequestor.request("GET",R,g).then(function(o){var e;if(t.oPromise!==P){e=new Error("Refresh canceled pending request: "+t);e.canceled=true;throw e;}return o;});if(D){D();}this.oPromise=P;}return this.oPromise.then(function(o){if(t.bSingleProperty){o=o?o.value:null;}if(p){return d(o,p,function(s){q.sap.log.error("Failed to drill-down into "+R+"/"+p+", invalid segment: "+s,null,"sap.ui.model.odata.v4.lib._Cache");});}return o;});};S.prototype.refresh=function(){if(this.bPost){throw new Error("Refresh not allowed when using POST");}this.oPromise=undefined;};S.prototype.toString=function(){return this.oRequestor.getServiceUrl()+this.sResourcePath;};S.prototype.update=function(g,p,v,e,P){var t=this;return this.oPromise.then(function(R){var b={},h;e+=C.buildQueryString(t.mQueryOptions,true);R=d(R,P);h={"If-Match":R["@odata.etag"]};b[p]=R[t.bSingleProperty?"value":p]=v;return t.oRequestor.request("PATCH",e,g,h,b).then(function(o){if(t.bSingleProperty){R.value=o[p];}else{for(p in R){if(p in o){R[p]=o[p];}}}return o;});});};C={buildQueryString:function(Q,D){return _.buildQuery(C.convertQueryOptions(Q,D));},convertExpand:function(e){var R=[];if(!e||typeof e!=="object"){throw new Error("$expand must be a valid object");}Object.keys(e).forEach(function(E){var v=e[E];if(v&&typeof v==="object"){R.push(C.convertExpandOptions(E,v));}else{R.push(E);}});return R.join(",");},convertExpandOptions:function(e,E){var b=[];c(E,function(o,O){b.push(o+'='+O);});return b.length?e+"("+b.join(";")+")":e;},convertQueryOptions:function(Q,D){var m={};if(!Q){return undefined;}c(Q,function(k,v){m[k]=v;},D);return m;},create:function _create(R,s,Q){return new a(R,s,Q);},createSingle:function _createSingle(R,s,Q,b,p){return new S(R,s,Q,b,p);}};return C;},false);