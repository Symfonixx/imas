import { Link, createInertiaApp, usePage } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import { computed, createSSRApp, h, mergeProps, onBeforeUnmount, onMounted, ref, unref, useSSRContext, watch } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderTeleport } from "vue/server-renderer";
import gsap$1 from "gsap";
import { ScrollTrigger as ScrollTrigger$1 } from "gsap/ScrollTrigger";
//#region vendor/tightenco/ziggy/dist/index.esm.js
function t(t, e) {
	for (var n = 0; n < e.length; n++) {
		var r = e[n];
		r.enumerable = r.enumerable || !1, r.configurable = !0, "value" in r && (r.writable = !0), Object.defineProperty(t, u(r.key), r);
	}
}
function e(e, n, r) {
	return n && t(e.prototype, n), r && t(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function n() {
	return n = Object.assign ? Object.assign.bind() : function(t) {
		for (var e = 1; e < arguments.length; e++) {
			var n = arguments[e];
			for (var r in n) ({}).hasOwnProperty.call(n, r) && (t[r] = n[r]);
		}
		return t;
	}, n.apply(null, arguments);
}
function r(t) {
	return r = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(t) {
		return t.__proto__ || Object.getPrototypeOf(t);
	}, r(t);
}
function o() {
	try {
		var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {}));
	} catch (t) {}
	return (o = function() {
		return !!t;
	})();
}
function i(t, e) {
	return i = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(t, e) {
		return t.__proto__ = e, t;
	}, i(t, e);
}
function u(t) {
	var e = function(t) {
		if ("object" != typeof t || !t) return t;
		var e = t[Symbol.toPrimitive];
		if (void 0 !== e) {
			var n = e.call(t, "string");
			if ("object" != typeof n) return n;
			throw new TypeError("@@toPrimitive must return a primitive value.");
		}
		return String(t);
	}(t);
	return "symbol" == typeof e ? e : e + "";
}
function f(t) {
	var e = "function" == typeof Map ? /* @__PURE__ */ new Map() : void 0;
	return f = function(t) {
		if (null === t || !function(t) {
			try {
				return -1 !== Function.toString.call(t).indexOf("[native code]");
			} catch (e) {
				return "function" == typeof t;
			}
		}(t)) return t;
		if ("function" != typeof t) throw new TypeError("Super expression must either be null or a function");
		if (void 0 !== e) {
			if (e.has(t)) return e.get(t);
			e.set(t, n);
		}
		function n() {
			return function(t, e, n) {
				if (o()) return Reflect.construct.apply(null, arguments);
				var r = [null];
				r.push.apply(r, e);
				var u = new (t.bind.apply(t, r))();
				return n && i(u, n.prototype), u;
			}(t, arguments, r(this).constructor);
		}
		return n.prototype = Object.create(t.prototype, { constructor: {
			value: n,
			enumerable: !1,
			writable: !0,
			configurable: !0
		} }), i(n, t);
	}, f(t);
}
var c = String.prototype.replace, a = /%20/g, l = {
	RFC1738: function(t) {
		return c.call(t, a, "+");
	},
	RFC3986: function(t) {
		return String(t);
	}
};
var s = "RFC3986";
var p = Object.prototype.hasOwnProperty, y = Array.isArray, d = /* @__PURE__ */ new WeakMap();
var b = function(t, e) {
	return d.set(t, e), t;
};
function v(t) {
	return d.has(t);
}
var h$1 = function(t) {
	return d.get(t);
}, m = function(t, e) {
	d.set(t, e);
};
var g = function() {
	const t = [];
	for (let e = 0; e < 256; ++e) t.push("%" + ((e < 16 ? "0" : "") + e.toString(16)).toUpperCase());
	return t;
}(), w = function(t, e) {
	const n = e && e.plainObjects ? Object.create(null) : {};
	for (let e = 0; e < t.length; ++e) void 0 !== t[e] && (n[e] = t[e]);
	return n;
}, j = function t(e, n, r) {
	if (!n) return e;
	if ("object" != typeof n) {
		if (y(e)) e.push(n);
		else {
			if (!e || "object" != typeof e) return [e, n];
			if (v(e)) {
				var o = h$1(e) + 1;
				e[o] = n, m(e, o);
			} else (r && (r.plainObjects || r.allowPrototypes) || !p.call(Object.prototype, n)) && (e[n] = !0);
		}
		return e;
	}
	if (!e || "object" != typeof e) {
		if (v(n)) {
			for (var i = Object.keys(n), u = r && r.plainObjects ? {
				__proto__: null,
				0: e
			} : { 0: e }, f = 0; f < i.length; f++) u[parseInt(i[f], 10) + 1] = n[i[f]];
			return b(u, h$1(n) + 1);
		}
		return [e].concat(n);
	}
	let c = e;
	return y(e) && !y(n) && (c = w(e, r)), y(e) && y(n) ? (n.forEach(function(n, o) {
		if (p.call(e, o)) {
			const i = e[o];
			i && "object" == typeof i && n && "object" == typeof n ? e[o] = t(i, n, r) : e.push(n);
		} else e[o] = n;
	}), e) : Object.keys(n).reduce(function(e, o) {
		const i = n[o];
		return e[o] = p.call(e, o) ? t(e[o], i, r) : i, e;
	}, c);
}, O = 1024, E = function(t, e, n, r) {
	if (v(t)) {
		var o = h$1(t) + 1;
		return t[o] = e, m(t, o), t;
	}
	var i = [].concat(t, e);
	return i.length > n ? b(w(i, { plainObjects: r }), i.length - 1) : i;
}, T = function(t, e) {
	if (y(t)) {
		const n = [];
		for (let r = 0; r < t.length; r += 1) n.push(e(t[r]));
		return n;
	}
	return e(t);
}, R = Object.prototype.hasOwnProperty, k = {
	brackets: function(t) {
		return t + "[]";
	},
	comma: "comma",
	indices: function(t, e) {
		return t + "[" + e + "]";
	},
	repeat: function(t) {
		return t;
	}
}, S = Array.isArray, I = Array.prototype.push, A = function(t, e) {
	I.apply(t, S(e) ? e : [e]);
}, D = Date.prototype.toISOString, $ = {
	addQueryPrefix: !1,
	allowDots: !1,
	allowEmptyArrays: !1,
	arrayFormat: "indices",
	charset: "utf-8",
	charsetSentinel: !1,
	delimiter: "&",
	encode: !0,
	encodeDotInKeys: !1,
	encoder: function(t, e, n, r, o) {
		if (0 === t.length) return t;
		let i = t;
		if ("symbol" == typeof t ? i = Symbol.prototype.toString.call(t) : "string" != typeof t && (i = String(t)), "iso-8859-1" === n) return escape(i).replace(/%u[0-9a-f]{4}/gi, function(t) {
			return "%26%23" + parseInt(t.slice(2), 16) + "%3B";
		});
		let u = "";
		for (let t = 0; t < i.length; t += O) {
			const e = i.length >= O ? i.slice(t, t + O) : i, n = [];
			for (let t = 0; t < e.length; ++t) {
				let r = e.charCodeAt(t);
				45 === r || 46 === r || 95 === r || 126 === r || r >= 48 && r <= 57 || r >= 65 && r <= 90 || r >= 97 && r <= 122 || "RFC1738" === o && (40 === r || 41 === r) ? n[n.length] = e.charAt(t) : r < 128 ? n[n.length] = g[r] : r < 2048 ? n[n.length] = g[192 | r >> 6] + g[128 | 63 & r] : r < 55296 || r >= 57344 ? n[n.length] = g[224 | r >> 12] + g[128 | r >> 6 & 63] + g[128 | 63 & r] : (t += 1, r = 65536 + ((1023 & r) << 10 | 1023 & e.charCodeAt(t)), n[n.length] = g[240 | r >> 18] + g[128 | r >> 12 & 63] + g[128 | r >> 6 & 63] + g[128 | 63 & r]);
			}
			u += n.join("");
		}
		return u;
	},
	encodeValuesOnly: !1,
	format: s,
	formatter: l[s],
	indices: !1,
	serializeDate: function(t) {
		return D.call(t);
	},
	skipNulls: !1,
	strictNullHandling: !1
}, N = {}, _ = function(t, e, n, r, o, i, u, f, c, a, l, s, p, y, d, b, v, h) {
	let m = t, g = h, w = 0, j = !1;
	for (; void 0 !== (g = g.get(N)) && !j;) {
		const e = g.get(t);
		if (w += 1, void 0 !== e) {
			if (e === w) throw new RangeError("Cyclic object value");
			j = !0;
		}
		void 0 === g.get(N) && (w = 0);
	}
	if ("function" == typeof a ? m = a(e, m) : m instanceof Date ? m = p(m) : "comma" === n && S(m) && (m = T(m, function(t) {
		return t instanceof Date ? p(t) : t;
	})), null === m) {
		if (i) return c && !b ? c(e, $.encoder, v, "key", y) : e;
		m = "";
	}
	if ("string" == typeof (O = m) || "number" == typeof O || "boolean" == typeof O || "symbol" == typeof O || "bigint" == typeof O || function(t) {
		return !(!t || "object" != typeof t || !(t.constructor && t.constructor.isBuffer && t.constructor.isBuffer(t)));
	}(m)) return c ? [d(b ? e : c(e, $.encoder, v, "key", y)) + "=" + d(c(m, $.encoder, v, "value", y))] : [d(e) + "=" + d(String(m))];
	var O;
	const E = [];
	if (void 0 === m) return E;
	let R;
	if ("comma" === n && S(m)) b && c && (m = T(m, c)), R = [{ value: m.length > 0 ? m.join(",") || null : void 0 }];
	else if (S(a)) R = a;
	else {
		const t = Object.keys(m);
		R = l ? t.sort(l) : t;
	}
	const k = f ? e.replace(/\./g, "%2E") : e, I = r && S(m) && 1 === m.length ? k + "[]" : k;
	if (o && S(m) && 0 === m.length) return I + "[]";
	for (let e = 0; e < R.length; ++e) {
		const g = R[e], j = "object" == typeof g && void 0 !== g.value ? g.value : m[g];
		if (u && null === j) continue;
		const O = s && f ? g.replace(/\./g, "%2E") : g, T = S(m) ? "function" == typeof n ? n(I, O) : I : I + (s ? "." + O : "[" + O + "]");
		h.set(t, w);
		const k = /* @__PURE__ */ new WeakMap();
		k.set(N, h), A(E, _(j, T, n, r, o, i, u, f, "comma" === n && b && S(m) ? null : c, a, l, s, p, y, d, b, v, k));
	}
	return E;
}, x = Object.prototype.hasOwnProperty, C = Array.isArray, P = {
	allowDots: !1,
	allowEmptyArrays: !1,
	allowPrototypes: !1,
	allowSparse: !1,
	arrayLimit: 20,
	charset: "utf-8",
	charsetSentinel: !1,
	comma: !1,
	decodeDotInKeys: !1,
	decoder: function(t, e, n) {
		const r = t.replace(/\+/g, " ");
		if ("iso-8859-1" === n) return r.replace(/%[0-9a-f]{2}/gi, unescape);
		try {
			return decodeURIComponent(r);
		} catch (t) {
			return r;
		}
	},
	delimiter: "&",
	depth: 5,
	duplicates: "combine",
	ignoreQueryPrefix: !1,
	interpretNumericEntities: !1,
	parameterLimit: 1e3,
	parseArrays: !0,
	plainObjects: !1,
	strictNullHandling: !1
}, Z = function(t) {
	return t.replace(/&#(\d+);/g, function(t, e) {
		return String.fromCharCode(parseInt(e, 10));
	});
}, F = function(t, e) {
	return t && "string" == typeof t && e.comma && t.indexOf(",") > -1 ? t.split(",") : t;
}, U = function(t, e, n, r) {
	if (!t) return;
	const o = n.allowDots ? t.replace(/\.([^.[]+)/g, "[$1]") : t, i = /(\[[^[\]]*])/g;
	let u = n.depth > 0 && /(\[[^[\]]*])/.exec(o);
	const f = u ? o.slice(0, u.index) : o, c = [];
	if (f) {
		if (!n.plainObjects && x.call(Object.prototype, f) && !n.allowPrototypes) return;
		c.push(f);
	}
	let a = 0;
	for (; n.depth > 0 && null !== (u = i.exec(o)) && a < n.depth;) {
		if (a += 1, !n.plainObjects && x.call(Object.prototype, u[1].slice(1, -1)) && !n.allowPrototypes) return;
		c.push(u[1]);
	}
	return u && c.push("[" + o.slice(u.index) + "]"), function(t, e, n, r) {
		let o = r ? e : F(e, n);
		for (let e = t.length - 1; e >= 0; --e) {
			let r;
			const i = t[e];
			if ("[]" === i && n.parseArrays) r = v(o) ? o : n.allowEmptyArrays && ("" === o || n.strictNullHandling && null === o) ? [] : E([], o, n.arrayLimit, n.plainObjects);
			else {
				r = n.plainObjects ? Object.create(null) : {};
				const t = "[" === i.charAt(0) && "]" === i.charAt(i.length - 1) ? i.slice(1, -1) : i, e = n.decodeDotInKeys ? t.replace(/%2E/g, ".") : t, u = parseInt(e, 10);
				n.parseArrays || "" !== e ? !isNaN(u) && i !== e && String(u) === e && u >= 0 && n.parseArrays && u <= n.arrayLimit ? (r = [], r[u] = o) : "__proto__" !== e && (r[e] = o) : r = { 0: o };
			}
			o = r;
		}
		return o;
	}(c, e, n, r);
};
function q(t, e) {
	const n = function(t) {
		if (!t) return P;
		if (void 0 !== t.allowEmptyArrays && "boolean" != typeof t.allowEmptyArrays) throw new TypeError("`allowEmptyArrays` option can only be `true` or `false`, when provided");
		if (void 0 !== t.decodeDotInKeys && "boolean" != typeof t.decodeDotInKeys) throw new TypeError("`decodeDotInKeys` option can only be `true` or `false`, when provided");
		if (null != t.decoder && "function" != typeof t.decoder) throw new TypeError("Decoder has to be a function.");
		if (void 0 !== t.charset && "utf-8" !== t.charset && "iso-8859-1" !== t.charset) throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");
		const e = void 0 === t.charset ? P.charset : t.charset, n = void 0 === t.duplicates ? P.duplicates : t.duplicates;
		if ("combine" !== n && "first" !== n && "last" !== n) throw new TypeError("The duplicates option must be either combine, first, or last");
		return {
			allowDots: void 0 === t.allowDots ? !0 === t.decodeDotInKeys || P.allowDots : !!t.allowDots,
			allowEmptyArrays: "boolean" == typeof t.allowEmptyArrays ? !!t.allowEmptyArrays : P.allowEmptyArrays,
			allowPrototypes: "boolean" == typeof t.allowPrototypes ? t.allowPrototypes : P.allowPrototypes,
			allowSparse: "boolean" == typeof t.allowSparse ? t.allowSparse : P.allowSparse,
			arrayLimit: "number" == typeof t.arrayLimit ? t.arrayLimit : P.arrayLimit,
			charset: e,
			charsetSentinel: "boolean" == typeof t.charsetSentinel ? t.charsetSentinel : P.charsetSentinel,
			comma: "boolean" == typeof t.comma ? t.comma : P.comma,
			decodeDotInKeys: "boolean" == typeof t.decodeDotInKeys ? t.decodeDotInKeys : P.decodeDotInKeys,
			decoder: "function" == typeof t.decoder ? t.decoder : P.decoder,
			delimiter: "string" == typeof t.delimiter || (r = t.delimiter, "[object RegExp]" === Object.prototype.toString.call(r)) ? t.delimiter : P.delimiter,
			depth: "number" == typeof t.depth || !1 === t.depth ? +t.depth : P.depth,
			duplicates: n,
			ignoreQueryPrefix: !0 === t.ignoreQueryPrefix,
			interpretNumericEntities: "boolean" == typeof t.interpretNumericEntities ? t.interpretNumericEntities : P.interpretNumericEntities,
			parameterLimit: "number" == typeof t.parameterLimit ? t.parameterLimit : P.parameterLimit,
			parseArrays: !1 !== t.parseArrays,
			plainObjects: "boolean" == typeof t.plainObjects ? t.plainObjects : P.plainObjects,
			strictNullHandling: "boolean" == typeof t.strictNullHandling ? t.strictNullHandling : P.strictNullHandling
		};
		var r;
	}(e);
	if ("" === t || null == t) return n.plainObjects ? Object.create(null) : {};
	const r = "string" == typeof t ? function(t, e) {
		const n = { __proto__: null }, r = (e.ignoreQueryPrefix ? t.replace(/^\?/, "") : t).split(e.delimiter, Infinity === e.parameterLimit ? void 0 : e.parameterLimit);
		let o, i = -1, u = e.charset;
		if (e.charsetSentinel) for (o = 0; o < r.length; ++o) 0 === r[o].indexOf("utf8=") && ("utf8=%E2%9C%93" === r[o] ? u = "utf-8" : "utf8=%26%2310003%3B" === r[o] && (u = "iso-8859-1"), i = o, o = r.length);
		for (o = 0; o < r.length; ++o) {
			if (o === i) continue;
			const t = r[o], f = t.indexOf("]="), c = -1 === f ? t.indexOf("=") : f + 1;
			let a, l;
			-1 === c ? (a = e.decoder(t, P.decoder, u, "key"), l = e.strictNullHandling ? null : "") : (a = e.decoder(t.slice(0, c), P.decoder, u, "key"), l = T(F(t.slice(c + 1), e), function(t) {
				return e.decoder(t, P.decoder, u, "value");
			})), l && e.interpretNumericEntities && "iso-8859-1" === u && (l = Z(l)), t.indexOf("[]=") > -1 && (l = C(l) ? [l] : l);
			const s = x.call(n, a);
			s && "combine" === e.duplicates ? n[a] = E(n[a], l, e.arrayLimit, e.plainObjects) : s && "last" !== e.duplicates || (n[a] = l);
		}
		return n;
	}(t, n) : t;
	let o = n.plainObjects ? Object.create(null) : {};
	const i = Object.keys(r);
	for (let e = 0; e < i.length; ++e) {
		const u = i[e], f = U(u, r[u], n, "string" == typeof t);
		o = j(o, f, n);
	}
	return !0 === n.allowSparse ? o : function(t) {
		const e = [{
			obj: { o: t },
			prop: "o"
		}], n = [];
		for (let t = 0; t < e.length; ++t) {
			const r = e[t], o = r.obj[r.prop], i = Object.keys(o);
			for (let t = 0; t < i.length; ++t) {
				const r = i[t], u = o[r];
				"object" == typeof u && null !== u && -1 === n.indexOf(u) && (e.push({
					obj: o,
					prop: r
				}), n.push(u));
			}
		}
		return function(t) {
			for (; t.length > 1;) {
				const e = t.pop(), n = e.obj[e.prop];
				if (y(n)) {
					const t = [];
					for (let e = 0; e < n.length; ++e) void 0 !== n[e] && t.push(n[e]);
					e.obj[e.prop] = t;
				}
			}
		}(e), t;
	}(o);
}
var K = /* @__PURE__ */ function() {
	function t(t, e, n) {
		var r, o;
		this.name = t, this.definition = e, this.bindings = null != (r = e.bindings) ? r : {}, this.wheres = null != (o = e.wheres) ? o : {}, this.config = n;
	}
	var n = t.prototype;
	return n.matchesUrl = function(t) {
		var e, n = this;
		if (!this.definition.methods.includes("GET")) return !1;
		var r = this.template.replace(/[.*+$()[\]]/g, "\\$&").replace(/(\/?){([^}?]*)(\??)}/g, function(t, e, r, o) {
			var i, u = "(?<" + r + ">" + ((null == (i = n.wheres[r]) ? void 0 : i.replace(/(^\^)|(\$$)/g, "")) || "[^/?]+") + ")";
			return o ? "(" + e + u + ")?" : "" + e + u;
		}).replace(/^\w+:\/\//, ""), o = t.replace(/^\w+:\/\//, "").split("?"), i = o[0], u = o[1], f = null != (e = new RegExp("^" + r + "/?$").exec(i)) ? e : new RegExp("^" + r + "/?$").exec(decodeURI(i));
		if (f) {
			for (var c in f.groups) f.groups[c] = "string" == typeof f.groups[c] ? decodeURIComponent(f.groups[c]) : f.groups[c];
			return {
				params: f.groups,
				query: q(u)
			};
		}
		return !1;
	}, n.compile = function(t) {
		var e = this;
		return this.parameterSegments.length ? this.template.replace(/{([^}?]+)(\??)}/g, function(n, r, o) {
			var i, u;
			if (!o && [null, void 0].includes(t[r])) throw new Error("Ziggy error: '" + r + "' parameter is required for route '" + e.name + "'.");
			if (e.wheres[r] && !new RegExp("^" + (o ? "(" + e.wheres[r] + ")?" : e.wheres[r]) + "$").test(null != (u = t[r]) ? u : "")) throw new Error("Ziggy error: '" + r + "' parameter '" + t[r] + "' does not match required format '" + e.wheres[r] + "' for route '" + e.name + "'.");
			return encodeURI(null != (i = t[r]) ? i : "").replace(/%7C/g, "|").replace(/%25/g, "%").replace(/\$/g, "%24");
		}).replace(this.config.absolute ? /(\.[^/]+?)(\/\/)/ : /(^)(\/\/)/, "$1/").replace(/\/+$/, "") : this.template;
	}, e(t, [
		{
			key: "template",
			get: function() {
				var t = (this.origin + "/" + this.definition.uri).replace(/\/+$/, "");
				return "" === t ? "/" : t;
			}
		},
		{
			key: "origin",
			get: function() {
				return this.config.absolute ? this.definition.domain ? "" + this.config.url.match(/^\w+:\/\//)[0] + this.definition.domain + (this.config.port ? ":" + this.config.port : "") : this.config.url : "";
			}
		},
		{
			key: "parameterSegments",
			get: function() {
				var t, e;
				return null != (t = null == (e = this.template.match(/{[^}?]+\??}/g)) ? void 0 : e.map(function(t) {
					return {
						name: t.replace(/{|\??}/g, ""),
						required: !/\?}$/.test(t)
					};
				})) ? t : [];
			}
		}
	]);
}(), z = /* @__PURE__ */ function(t) {
	function r(e, r, o, i) {
		var u;
		if (void 0 === o && (o = !0), (u = t.call(this) || this).t = null != i ? i : "undefined" != typeof Ziggy ? Ziggy : null == globalThis ? void 0 : globalThis.Ziggy, !u.t && "undefined" != typeof document && document.getElementById("ziggy-routes-json") && (globalThis.Ziggy = JSON.parse(document.getElementById("ziggy-routes-json").textContent), u.t = globalThis.Ziggy), u.t = n({}, u.t, { absolute: o }), e) {
			if (!u.t.routes[e]) throw new Error("Ziggy error: route '" + e + "' is not in the route list.");
			u.i = new K(e, u.t.routes[e], u.t), u.u = u.l(r);
		}
		return u;
	}
	var o, u = t;
	(o = r).prototype = Object.create(u.prototype), o.prototype.constructor = o, i(o, u);
	var f = r.prototype;
	return f.toString = function() {
		var t = this, e = Object.keys(this.u).filter(function(e) {
			return !t.i.parameterSegments.some(function(t) {
				return t.name === e;
			});
		}).filter(function(t) {
			return "_query" !== t;
		}).reduce(function(e, r) {
			var o;
			return n({}, e, ((o = {})[r] = t.u[r], o));
		}, {});
		return this.i.compile(this.u) + function(t, e) {
			let n = t;
			const r = function(t) {
				if (!t) return $;
				if (void 0 !== t.allowEmptyArrays && "boolean" != typeof t.allowEmptyArrays) throw new TypeError("`allowEmptyArrays` option can only be `true` or `false`, when provided");
				if (void 0 !== t.encodeDotInKeys && "boolean" != typeof t.encodeDotInKeys) throw new TypeError("`encodeDotInKeys` option can only be `true` or `false`, when provided");
				if (null != t.encoder && "function" != typeof t.encoder) throw new TypeError("Encoder has to be a function.");
				const e = t.charset || $.charset;
				if (void 0 !== t.charset && "utf-8" !== t.charset && "iso-8859-1" !== t.charset) throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");
				let n = s;
				if (void 0 !== t.format) {
					if (!R.call(l, t.format)) throw new TypeError("Unknown format option provided.");
					n = t.format;
				}
				const r = l[n];
				let o, i = $.filter;
				if (("function" == typeof t.filter || S(t.filter)) && (i = t.filter), o = t.arrayFormat in k ? t.arrayFormat : "indices" in t ? t.indices ? "indices" : "repeat" : $.arrayFormat, "commaRoundTrip" in t && "boolean" != typeof t.commaRoundTrip) throw new TypeError("`commaRoundTrip` must be a boolean, or absent");
				return {
					addQueryPrefix: "boolean" == typeof t.addQueryPrefix ? t.addQueryPrefix : $.addQueryPrefix,
					allowDots: void 0 === t.allowDots ? !0 === t.encodeDotInKeys || $.allowDots : !!t.allowDots,
					allowEmptyArrays: "boolean" == typeof t.allowEmptyArrays ? !!t.allowEmptyArrays : $.allowEmptyArrays,
					arrayFormat: o,
					charset: e,
					charsetSentinel: "boolean" == typeof t.charsetSentinel ? t.charsetSentinel : $.charsetSentinel,
					commaRoundTrip: t.commaRoundTrip,
					delimiter: void 0 === t.delimiter ? $.delimiter : t.delimiter,
					encode: "boolean" == typeof t.encode ? t.encode : $.encode,
					encodeDotInKeys: "boolean" == typeof t.encodeDotInKeys ? t.encodeDotInKeys : $.encodeDotInKeys,
					encoder: "function" == typeof t.encoder ? t.encoder : $.encoder,
					encodeValuesOnly: "boolean" == typeof t.encodeValuesOnly ? t.encodeValuesOnly : $.encodeValuesOnly,
					filter: i,
					format: n,
					formatter: r,
					serializeDate: "function" == typeof t.serializeDate ? t.serializeDate : $.serializeDate,
					skipNulls: "boolean" == typeof t.skipNulls ? t.skipNulls : $.skipNulls,
					sort: "function" == typeof t.sort ? t.sort : null,
					strictNullHandling: "boolean" == typeof t.strictNullHandling ? t.strictNullHandling : $.strictNullHandling
				};
			}(e);
			let o, i;
			"function" == typeof r.filter ? (i = r.filter, n = i("", n)) : S(r.filter) && (i = r.filter, o = i);
			const u = [];
			if ("object" != typeof n || null === n) return "";
			const f = k[r.arrayFormat], c = "comma" === f && r.commaRoundTrip;
			o || (o = Object.keys(n)), r.sort && o.sort(r.sort);
			const a = /* @__PURE__ */ new WeakMap();
			for (let t = 0; t < o.length; ++t) {
				const e = o[t];
				r.skipNulls && null === n[e] || A(u, _(n[e], e, f, c, r.allowEmptyArrays, r.strictNullHandling, r.skipNulls, r.encodeDotInKeys, r.encode ? r.encoder : null, r.filter, r.sort, r.allowDots, r.serializeDate, r.format, r.formatter, r.encodeValuesOnly, r.charset, a));
			}
			const p = u.join(r.delimiter);
			let y = !0 === r.addQueryPrefix ? "?" : "";
			return r.charsetSentinel && (y += "iso-8859-1" === r.charset ? "utf8=%26%2310003%3B&" : "utf8=%E2%9C%93&"), p.length > 0 ? y + p : "";
		}(n({}, e, this.u._query), {
			addQueryPrefix: !0,
			arrayFormat: "indices",
			encodeValuesOnly: !0,
			skipNulls: !0,
			encoder: function(t, e) {
				return "boolean" == typeof t ? Number(t) : e(t);
			}
		});
	}, f.p = function(t) {
		var e = this;
		t ? this.t.absolute && t.startsWith("/") && (t = this.v().host + t) : t = this.h();
		var r = {}, o = Object.entries(this.t.routes).find(function(n) {
			return r = new K(n[0], n[1], e.t).matchesUrl(t);
		}) || [void 0, void 0];
		return n({ name: o[0] }, r, { route: o[1] });
	}, f.h = function() {
		var t = this.v(), e = t.pathname, n = t.search;
		return (this.t.absolute ? t.host + e : e.replace(this.t.url.replace(/^\w*:\/\/[^/]+/, ""), "").replace(/^\/+/, "/")) + n;
	}, f.current = function(t, e) {
		var r = this.p(), o = r.name, i = r.params, u = r.query, f = r.route;
		if (!t) return o;
		var c = new RegExp("^" + t.replace(/\./g, "\\.").replace(/\*/g, ".*") + "$").test(o);
		if ([null, void 0].includes(e) || !c) return c;
		var a = new K(o, f, this.t);
		e = this.l(e, a);
		var l = n({}, i, u);
		if (Object.values(e).every(function(t) {
			return !t;
		}) && !Object.values(l).some(function(t) {
			return void 0 !== t;
		})) return !0;
		var s = function(t, e) {
			return Object.entries(t).every(function(t) {
				var n = t[0], r = t[1];
				return Array.isArray(r) && Array.isArray(e[n]) ? r.every(function(t) {
					return e[n].includes(t) || e[n].includes(decodeURIComponent(t));
				}) : "object" == typeof r && "object" == typeof e[n] && null !== r && null !== e[n] ? s(r, e[n]) : e[n] == r || e[n] == decodeURIComponent(r);
			});
		};
		return s(e, l);
	}, f.v = function() {
		var t, e, n, r, o, i, u = "undefined" != typeof window ? window.location : {}, f = u.host, c = u.pathname, a = u.search;
		return {
			host: null != (t = null == (e = this.t.location) ? void 0 : e.host) ? t : void 0 === f ? "" : f,
			pathname: null != (n = null == (r = this.t.location) ? void 0 : r.pathname) ? n : void 0 === c ? "" : c,
			search: null != (o = null == (i = this.t.location) ? void 0 : i.search) ? o : void 0 === a ? "" : a
		};
	}, f.has = function(t) {
		return this.t.routes.hasOwnProperty(t);
	}, f.l = function(t, e) {
		var r = this;
		void 0 === t && (t = {}), void 0 === e && (e = this.i), t ??= {}, t = ["string", "number"].includes(typeof t) ? [t] : t;
		var o = e.parameterSegments.filter(function(t) {
			return !r.t.defaults[t.name];
		});
		if (Array.isArray(t)) t = t.reduce(function(t, e, r) {
			var i, u;
			return n({}, t, o[r] ? ((i = {})[o[r].name] = e, i) : "object" == typeof e ? e : ((u = {})[e] = "", u));
		}, {});
		else if (1 === o.length && !t.hasOwnProperty(o[0].name) && (t.hasOwnProperty(Object.values(e.bindings)[0]) || t.hasOwnProperty("id"))) {
			var i;
			(i = {})[o[0].name] = t, t = i;
		}
		return n({}, this.m(e), this.j(t, e));
	}, f.m = function(t) {
		var e = this;
		return t.parameterSegments.filter(function(t) {
			return e.t.defaults[t.name];
		}).reduce(function(t, r, o) {
			var i, u = r.name;
			return n({}, t, ((i = {})[u] = e.t.defaults[u], i));
		}, {});
	}, f.j = function(t, e) {
		var r = e.bindings, o = e.parameterSegments;
		return Object.entries(t).reduce(function(t, e) {
			var i, u, f = e[0], c = e[1];
			if (!c || "object" != typeof c || Array.isArray(c) || !o.some(function(t) {
				return t.name === f;
			})) return n({}, t, ((u = {})[f] = c, u));
			var a = c.hasOwnProperty(r[f]) ? r[f] : c.hasOwnProperty("id") ? "id" : void 0;
			if (void 0 === a) throw new Error("Ziggy error: object passed as '" + f + "' parameter is missing route model binding key '" + r[f] + "'.");
			return n({}, t, ((i = {})[f] = c[a], i));
		}, {});
	}, f.valueOf = function() {
		return this.toString();
	}, e(r, [
		{
			key: "params",
			get: function() {
				var t = this.p();
				return n({}, t.params, t.query);
			}
		},
		{
			key: "routeParams",
			get: function() {
				return this.p().params;
			}
		},
		{
			key: "queryParams",
			get: function() {
				return this.p().query;
			}
		}
	]);
}(/* @__PURE__ */ f(String));
function B(t, e, n, r) {
	var o = new z(t, e, n, r);
	return t ? o.toString() : o;
}
var M = { install: function(t, e) {
	var n = function(t, n, r, o) {
		return void 0 === o && (o = e), B(t, n, r, o);
	};
	parseInt(t.version) > 2 ? (t.config.globalProperties.route = n, t.provide("route", n)) : t.mixin({ methods: { route: n } });
} };
//#endregion
//#region resources/js/composables/useOpenAuthModal.js
/** Dispatched on `document`; handled by `UserNavbar` to open `AuthModal`. */
var IMAS_OPEN_AUTH_EVENT = "imas:open-auth";
/**
* Open the front-office sign-in / register / forgot / reset modal from anywhere.
* Requires `UserNavbar` (or another listener) to be mounted.
*
* @param {"login" | "register" | "forgot" | "reset"} [tab]
*/
function useOpenAuthModal() {
	function openAuthModal(tab = "login") {
		if (typeof document === "undefined") return;
		const normalized = tab === "register" || tab === "reset" || tab === "forgot" ? tab : "login";
		document.dispatchEvent(new CustomEvent(IMAS_OPEN_AUTH_EVENT, {
			detail: { tab: normalized },
			bubbles: true
		}));
	}
	return { openAuthModal };
}
//#endregion
//#region resources/js/utils/videoEmbed.js
/**
* Resolve a front-office video URL into an embeddable iframe src or direct file URL.
*
* @param {string|null|undefined} raw
* @returns {{ type: 'iframe', src: string }|{ type: 'file', src: string }|null}
*/
function resolveVideoPlayback(raw) {
	if (typeof raw !== "string") return null;
	const trimmed = raw.trim();
	if (trimmed === "") return null;
	const iframeSrc = extractIframeSrc(trimmed);
	if (iframeSrc) return {
		type: "iframe",
		src: normalizeEmbedUrl(iframeSrc)
	};
	if (isEmbeddableUrl(trimmed)) return {
		type: "iframe",
		src: normalizeEmbedUrl(trimmed)
	};
	const youtubeId = extractYoutubeId(trimmed);
	if (youtubeId) return {
		type: "iframe",
		src: `https://www.youtube.com/embed/${youtubeId}`
	};
	const vimeoId = extractVimeoId(trimmed);
	if (vimeoId) return {
		type: "iframe",
		src: `https://player.vimeo.com/video/${vimeoId}`
	};
	if (/\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(trimmed)) return {
		type: "file",
		src: trimmed
	};
	return null;
}
/**
* Build a muted, looping YouTube embed URL for a full-bleed hero background.
*
* @param {string|null|undefined} raw Admin iframe HTML, watch URL, or embed URL
* @returns {string|null}
*/
function resolveYoutubeHeroBackgroundSrc(raw) {
	const playback = resolveVideoPlayback(raw);
	if (!playback || playback.type !== "iframe") return null;
	const embedSrc = playback.src;
	if (!/youtube(-nocookie)?\.com\/embed\//i.test(embedSrc)) return null;
	const videoId = extractYoutubeId(String(raw ?? "")) || extractYoutubeId(embedSrc);
	if (!videoId) return null;
	try {
		const url = new URL(`https://www.youtube.com/embed/${videoId}`);
		url.searchParams.set("autoplay", "1");
		url.searchParams.set("mute", "1");
		url.searchParams.set("controls", "0");
		url.searchParams.set("loop", "1");
		url.searchParams.set("playlist", videoId);
		url.searchParams.set("playsinline", "1");
		url.searchParams.set("modestbranding", "1");
		url.searchParams.set("rel", "0");
		url.searchParams.set("showinfo", "0");
		url.searchParams.set("disablekb", "1");
		url.searchParams.set("fs", "0");
		url.searchParams.set("iv_load_policy", "3");
		url.searchParams.set("enablejsapi", "1");
		if (typeof window !== "undefined" && window.location?.origin) url.searchParams.set("origin", window.location.origin);
		return url.toString();
	} catch {
		return null;
	}
}
/**
* @param {string} embedSrc
* @param {{ autoplay?: boolean }} [options]
*/
function withVideoAutoplay(embedSrc, options = {}) {
	if (!(options.autoplay !== false) || typeof embedSrc !== "string" || embedSrc === "") return embedSrc;
	try {
		const url = new URL(embedSrc, window.location.origin);
		url.searchParams.set("autoplay", "1");
		url.searchParams.set("rel", "0");
		if (url.hostname.includes("youtube.com")) url.searchParams.set("modestbranding", "1");
		return url.toString();
	} catch {
		return `${embedSrc}${embedSrc.includes("?") ? "&" : "?"}autoplay=1`;
	}
}
function extractIframeSrc(value) {
	return value.match(/<iframe[^>]+src=["']([^"']+)["']/i)?.[1]?.trim() ?? "";
}
function isEmbeddableUrl(value) {
	return /youtube(-nocookie)?\.com\/embed\//i.test(value) || /player\.vimeo\.com\/video\//i.test(value);
}
function extractYoutubeId(value) {
	for (const pattern of [
		/youtube(?:-nocookie)?\.com\/embed\/([a-zA-Z0-9_-]{11})/i,
		/youtube\.com\/watch\?[^#]*v=([a-zA-Z0-9_-]{11})/i,
		/youtu\.be\/([a-zA-Z0-9_-]{11})/i,
		/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/i
	]) {
		const match = value.match(pattern);
		if (match?.[1]) return match[1];
	}
	return null;
}
function extractVimeoId(value) {
	return value.match(/vimeo\.com\/(?:video\/)?(\d+)/i)?.[1] ?? null;
}
function normalizeEmbedUrl(src) {
	if (src.startsWith("//")) return `https:${src}`;
	return src;
}
//#endregion
//#region \0plugin-vue:export-helper
var _plugin_vue_export_helper_default = (sfc, props) => {
	const target = sfc.__vccOpts || sfc;
	for (const [key, val] of props) target[key] = val;
	return target;
};
//#endregion
//#region resources/js/components/Global/VideoLightbox.vue
var _sfc_main$2 = {
	__name: "VideoLightbox",
	__ssrInlineRender: true,
	props: {
		modelValue: {
			type: Boolean,
			default: false
		},
		videoUrl: {
			type: String,
			default: ""
		},
		ariaLabel: {
			type: String,
			default: "Video player"
		},
		invalidMessage: {
			type: String,
			default: "Video is not available."
		}
	},
	emits: ["update:modelValue"],
	setup(__props, { emit: __emit }) {
		const props = __props;
		const page = usePage();
		const closeLabel = computed(() => page.props.translations?.Close || page.props.translations?.close || "Close");
		const playback = computed(() => resolveVideoPlayback(props.videoUrl));
		const activeSrc = computed(() => {
			if (!props.modelValue || !playback.value) return "";
			if (playback.value.type === "iframe") return withVideoAutoplay(playback.value.src);
			return playback.value.src;
		});
		function lockBodyScroll(lock) {
			if (typeof document === "undefined") return;
			document.body.style.overflow = lock ? "hidden" : "";
		}
		watch(() => props.modelValue, (open) => {
			lockBodyScroll(open);
		});
		/**
		* Gate `<Teleport to="body">` until after mount so Inertia SSR (which drops
		* teleport-to-body content) and the client's first render stay identical.
		*/
		const mounted = ref(false);
		onMounted(() => {
			mounted.value = true;
		});
		onBeforeUnmount(() => {
			lockBodyScroll(false);
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (mounted.value) ssrRenderTeleport(_push, (_push) => {
				if (__props.modelValue) {
					_push(`<div class="imas-video-lightbox" role="dialog" aria-modal="true"${ssrRenderAttr("aria-label", __props.ariaLabel)} data-v-75f8f886><button type="button" class="imas-video-lightbox__backdrop"${ssrRenderAttr("aria-label", closeLabel.value)} data-v-75f8f886></button><div class="imas-video-lightbox__dialog" data-v-75f8f886><button type="button" class="imas-video-lightbox__close"${ssrRenderAttr("aria-label", closeLabel.value)} data-v-75f8f886><i class="fa fa-times" aria-hidden="true" data-v-75f8f886></i></button><div class="imas-video-lightbox__content" data-v-75f8f886>`);
					if (playback.value?.type === "iframe" && activeSrc.value) _push(`<iframe${ssrRenderAttr("src", activeSrc.value)} class="imas-video-lightbox__iframe"${ssrRenderAttr("title", __props.ariaLabel)} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" data-v-75f8f886></iframe>`);
					else if (playback.value?.type === "file" && activeSrc.value) _push(`<video class="imas-video-lightbox__video"${ssrRenderAttr("src", activeSrc.value)} controls playsinline data-v-75f8f886></video>`);
					else _push(`<p class="imas-video-lightbox__error text-center mb-0" data-v-75f8f886>${ssrInterpolate(__props.invalidMessage)}</p>`);
					_push(`</div></div></div>`);
				} else _push(`<!---->`);
			}, "body", false, _parent);
			else _push(`<!---->`);
		};
	}
};
var _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/VideoLightbox.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var VideoLightbox_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["__scopeId", "data-v-75f8f886"]]);
//#endregion
//#region Modules/Property/resources/assets/js/utils/propertyLocalized.js
/**
* @param {string|Record<string, string>|null|undefined} value
* @param {string} locale
*/
function localizedField(value, locale = "en") {
	if (typeof value === "string") return value.trim();
	if (value && typeof value === "object") {
		const raw = value[locale] ?? value.en ?? Object.values(value).find((v) => typeof v === "string");
		return typeof raw === "string" ? raw.trim() : "";
	}
	return "";
}
//#endregion
//#region Modules/Property/resources/assets/js/utils/propertyLocation.js
/**
* @param {string|Record<string, string>|null|undefined} name
* @param {string} locale
*/
function localizedLocationName(name, locale = "en") {
	if (typeof name === "string") return name.trim();
	if (name && typeof name === "object") {
		const value = name[locale] ?? name.en ?? Object.values(name)[0];
		return typeof value === "string" ? value.trim() : "";
	}
	return "";
}
/**
* @param {{ city?: { name?: unknown }, district?: { name?: unknown }, area?: { name?: unknown } }|null|undefined} location
* @param {string} locale
*/
function propertyLocationLine(location, locale = "en") {
	if (!location) return "";
	return [
		localizedLocationName(location.city?.name, locale),
		localizedLocationName(location.district?.name, locale),
		localizedLocationName(location.area?.name, locale)
	].filter(Boolean).join(", ");
}
//#endregion
//#region Modules/Property/resources/assets/js/utils/propertyPrice.js
/**
* Lowest unit-type price exposed as `start_price` on listing cards.
*
* @param {{ start_price?: unknown, price?: unknown }|null|undefined} property
*/
function propertyStartPrice(property) {
	const start = Number(property?.start_price);
	if (Number.isFinite(start)) return start;
	const fallback = Number(property?.price);
	return Number.isFinite(fallback) ? fallback : null;
}
/**
* Format a property price with a plain `$` symbol (not locale “US$” / “USD”).
*
* @param {unknown} amount
* @param {string} [locale="en"]
* @returns {string}
*/
function formatPropertyMoney(amount, locale = "en") {
	const n = Number(amount);
	if (!Number.isFinite(n)) return "—";
	return `$${new Intl.NumberFormat(locale, {
		minimumFractionDigits: 0,
		maximumFractionDigits: 0
	}).format(n)}`;
}
//#endregion
//#region resources/js/plugins/gsap.js
gsap$1.registerPlugin(ScrollTrigger$1);
/** Shared defaults for IMas front-office animations. */
gsap$1.defaults({
	ease: "power2.out",
	duration: 1
});
/**
* @returns {boolean}
*/
function prefersReducedMotion() {
	return typeof window !== "undefined" && window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches === true;
}
/**
* Run `fn` inside a scoped GSAP context (auto-reverted on `revert()`).
* Skips animation when the user prefers reduced motion.
*
* @param {() => void} fn
* @param {import('vue').ComponentPublicInstance | Element | string | null | undefined} scope
* @returns {import('gsap').Context | { revert: () => void }}
*/
function createGsapContext(fn, scope) {
	if (prefersReducedMotion()) return { revert() {} };
	return gsap$1.context(fn, scope ?? void 0);
}
/**
* Refresh ScrollTrigger after layout changes (images, fonts, Inertia page swap).
*/
function refreshScrollTrigger() {
	if (prefersReducedMotion()) return;
	requestAnimationFrame(() => {
		ScrollTrigger$1.refresh();
	});
}
var gsap_default = { install(app) {
	app.config.globalProperties.$gsap = gsap$1;
	app.config.globalProperties.$ScrollTrigger = ScrollTrigger$1;
	app.provide("gsap", gsap$1);
	app.provide("ScrollTrigger", ScrollTrigger$1);
} };
//#endregion
//#region Modules/Property/resources/assets/js/utils/propertyUnitType.js
/**
* @param {number|string|null|undefined} value
*/
function formatAreaNumber(value) {
	const n = Number(value);
	if (!Number.isFinite(n)) return null;
	return new Intl.NumberFormat(void 0, { maximumFractionDigits: 0 }).format(n);
}
/**
* @param {number|string|null|undefined} min
* @param {number|string|null|undefined} max
*/
function unitTypeAreaRange(min, max) {
	const minS = formatAreaNumber(min);
	const maxS = formatAreaNumber(max);
	if (minS && maxS && minS !== maxS) {
		const lo = Math.min(Number(min), Number(max));
		const hi = Math.max(Number(min), Number(max));
		return `${formatAreaNumber(lo)} - ${formatAreaNumber(hi)} m²`;
	}
	const single = minS ?? maxS;
	return single ? `${single} m²` : "";
}
/**
* @param {{ name?: string, min_area?: unknown, max_area?: unknown }|null|undefined} unitType
* @return {{ name: string, area: string }}
*/
function unitTypeDisplayParts(unitType) {
	if (!unitType) return {
		name: "—",
		area: ""
	};
	return {
		name: String(unitType.name ?? "").trim() || "—",
		area: unitTypeAreaRange(unitType.min_area, unitType.max_area)
	};
}
//#endregion
//#region Modules/Property/resources/assets/js/components/PropertyCardUnitTypesBar.vue
var _sfc_main$1 = {
	__name: "PropertyCardUnitTypesBar",
	__ssrInlineRender: true,
	props: { unitTypes: {
		type: Array,
		default: () => []
	} },
	setup(__props) {
		const props = __props;
		const page = usePage();
		const trans = (key) => page.props.translations[key] || key;
		const activeIndex = ref(0);
		let rotateTimer = null;
		const activeUnit = computed(() => unitTypeDisplayParts(props.unitTypes[activeIndex.value]));
		const countLabel = computed(() => {
			const n = props.unitTypes.length;
			if (n === 1) return trans("properties.unit_types_count_one");
			const template = trans("properties.unit_types_count");
			return template.includes(":count") ? template.replace(":count", String(n)) : `${n} ${template}`;
		});
		function clearRotateTimer() {
			if (rotateTimer !== null) {
				clearInterval(rotateTimer);
				rotateTimer = null;
			}
		}
		function startRotateTimer() {
			clearRotateTimer();
			activeIndex.value = 0;
			if (props.unitTypes.length <= 1 || prefersReducedMotion()) return;
			rotateTimer = setInterval(() => {
				activeIndex.value = (activeIndex.value + 1) % props.unitTypes.length;
			}, 3e3);
		}
		watch(() => props.unitTypes, () => startRotateTimer(), { deep: true });
		onMounted(() => startRotateTimer());
		onBeforeUnmount(() => clearRotateTimer());
		return (_ctx, _push, _parent, _attrs) => {
			if (__props.unitTypes.length > 0) {
				_push(`<div${ssrRenderAttrs(mergeProps({
					class: "imas-unit-types-bar text-base pb-3",
					role: "group",
					"aria-label": trans("properties.unit_types_aria")
				}, _attrs))} data-v-6b93fea9><div class="imas-unit-types-bar__left" data-v-6b93fea9><i class="fa fa-building imas-unit-types-bar__icon" aria-hidden="true" data-v-6b93fea9></i><div class="imas-unit-types-flip" aria-live="polite" data-v-6b93fea9><div class="imas-unit-types-flip__slide" data-v-6b93fea9><span class="imas-unit-types-flip__name" data-v-6b93fea9>${ssrInterpolate(activeUnit.value.name)}</span>`);
				if (activeUnit.value.area) _push(`<span class="imas-unit-types-flip__sep" aria-hidden="true" data-v-6b93fea9>→</span>`);
				else _push(`<!---->`);
				if (activeUnit.value.area) _push(`<span class="imas-unit-types-flip__area" dir="ltr" data-v-6b93fea9>${ssrInterpolate(activeUnit.value.area)}</span>`);
				else _push(`<!---->`);
				_push(`</div></div></div><span class="imas-unit-types-bar__count" data-v-6b93fea9><i class="fa fa-circle imas-unit-types-bar__dot" aria-hidden="true" data-v-6b93fea9></i> ${ssrInterpolate(countLabel.value)}</span></div>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyCardUnitTypesBar.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
var PropertyCardUnitTypesBar_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$1, [["__scopeId", "data-v-6b93fea9"]]);
//#endregion
//#region Modules/Property/resources/assets/js/components/PropertyCard.vue
var _sfc_main = {
	__name: "PropertyCard",
	__ssrInlineRender: true,
	props: {
		property: {
			type: Object,
			required: true
		},
		columnClass: {
			type: String,
			default: "col-lg-4 col-md-6 col-xs-12"
		}
	},
	setup(__props) {
		const props = __props;
		const page = usePage();
		const { openAuthModal } = useOpenAuthModal();
		const trans = (key) => page.props.translations[key] || key;
		const locale = computed(() => page.props.locale || "en");
		computed(() => page.props.auth != null);
		const isSoldOut = computed(() => Boolean(props.property.is_sold_out));
		const localFavorited = ref(Boolean(props.property.is_favorited));
		const videoLightboxOpen = ref(false);
		watch(() => props.property.is_favorited, (v) => {
			localFavorited.value = Boolean(v);
		});
		const favoriteAriaLabel = computed(() => localFavorited.value ? trans("properties.remove_favorite") : trans("properties.add_favorite"));
		const propertyTypeLabel = computed(() => {
			const type = props.property.property_type;
			if (!type) return "";
			return localizedField(type.name, locale.value);
		});
		const displayTitle = computed(() => {
			const t = props.property.title;
			return typeof t === "string" && t.trim() !== "" ? t : props.property.project_name || props.property.project_code || "Property";
		});
		const showUrl = computed(() => {
			if (typeof props.property.url === "string" && props.property.url.trim() !== "") return props.property.url;
			try {
				if (typeof route === "function" && route().has?.("property.show")) {
					const slug = props.property.url_key || props.property.slug || props.property.project_code;
					if (slug) return route("property.show", slug);
				}
			} catch {}
			const slug = props.property.url_key || props.property.slug || props.property.project_code;
			return slug ? `/property/${encodeURIComponent(slug)}` : "#";
		});
		const addressLine = computed(() => {
			const line = propertyLocationLine(props.property.location, locale.value);
			return line !== "" ? line : "—";
		});
		function stripHtml(value) {
			if (typeof value !== "string") return "";
			return value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
		}
		const overviewText = computed(() => stripHtml(localizedLocationName(props.property.overview, locale.value)));
		function formatMoney(amount) {
			return formatPropertyMoney(amount, locale.value);
		}
		const priceAmount = computed(() => formatMoney(propertyStartPrice(props.property)));
		const playVideoLabel = computed(() => trans("property_show.play_video"));
		const videoInvalidMessage = computed(() => trans("property_show.video_unavailable"));
		const videoLightboxAria = computed(() => `${playVideoLabel.value} – ${displayTitle.value}`);
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: ["imas-property-card imas-property-card--media-overlay item user-select-none", [__props.columnClass, { "imas-property-card--sold-out": isSoldOut.value }]] }, _attrs))} data-v-6b3d484b><div class="project-single imas-card__surface" data-v-6b3d484b>`);
			if (!isSoldOut.value) _push(ssrRenderComponent(unref(Link), {
				href: showUrl.value,
				class: "imas-property-card__stretched-link",
				"aria-label": displayTitle.value
			}, null, _parent));
			else _push(`<!---->`);
			_push(`<div class="project-inner project-head imas-card__media" data-v-6b3d484b><div class="homes" data-v-6b3d484b><div class="homes-img" data-v-6b3d484b>`);
			if (propertyTypeLabel.value || __props.property.is_featured) {
				_push(`<div class="homes-tag button alt imas-badge--type" data-v-6b3d484b>`);
				if (__props.property.is_featured) _push(`<i class="fa fa-star imas-featured-star" aria-hidden="true" data-v-6b3d484b></i>`);
				else _push(`<!---->`);
				if (__props.property.is_featured) _push(`<span class="visually-hidden" data-v-6b3d484b>${ssrInterpolate(trans("properties.featured"))}</span>`);
				else _push(`<!---->`);
				if (propertyTypeLabel.value) _push(`<span data-v-6b3d484b>${ssrInterpolate(propertyTypeLabel.value)}</span>`);
				else _push(`<!---->`);
				_push(`</div>`);
			} else _push(`<!---->`);
			if (__props.property.is_sold_out) _push(`<div class="homes-tag button alt imas-sold-out-badge imas-badge--danger" data-v-6b3d484b>${ssrInterpolate(trans("properties.sold_out"))}</div>`);
			else _push(`<!---->`);
			_push(`<img${ssrRenderAttr("src", __props.property.thumbnail_url)}${ssrRenderAttr("alt", __props.property.thumbnail_alt || displayTitle.value)}${ssrRenderAttr("title", __props.property.thumbnail_title || void 0)} class="img-responsive" data-v-6b3d484b></div></div><div class="imas-card-actions" data-v-6b3d484b><div class="homes-price imas-start-price imas-chip" data-v-6b3d484b><span class="imas-start-price__from" data-v-6b3d484b>${ssrInterpolate(trans("properties.price_from"))}</span><span class="imas-start-price__amount" data-v-6b3d484b>${ssrInterpolate(priceAmount.value)}</span></div>`);
			if (!isSoldOut.value) {
				_push(`<div class="button-effect" data-v-6b3d484b>`);
				if (__props.property.youtube_video_url) _push(`<button type="button" class="btn imas-card-video-btn"${ssrRenderAttr("aria-label", playVideoLabel.value)} data-v-6b3d484b><i class="fas fa-video" aria-hidden="true" data-v-6b3d484b></i></button>`);
				else _push(`<!---->`);
				_push(`<button type="button" class="${ssrRenderClass([{ "is-favorited": localFavorited.value }, "btn imas-favorite-btn"])}"${ssrRenderAttr("aria-label", favoriteAriaLabel.value)}${ssrRenderAttr("aria-pressed", localFavorited.value)} data-v-6b3d484b><i class="${ssrRenderClass([localFavorited.value ? "fa-heart" : "fa-heart-o", "fa favorite-icon"])}" aria-hidden="true" data-v-6b3d484b></i></button></div>`);
			} else _push(`<!---->`);
			_push(`</div></div><div class="homes-content imas-card__body" data-v-6b3d484b><h3 class="imas-property-title imas-card__title" data-v-6b3d484b><span class="imas-card__title-text" data-v-6b3d484b>${ssrInterpolate(displayTitle.value)}</span></h3>`);
			if (overviewText.value) _push(`<p class="imas-property-overview imas-card__excerpt text-card-excerpt mb-3" data-v-6b3d484b>${ssrInterpolate(overviewText.value)}</p>`);
			else _push(`<!---->`);
			_push(`<p class="homes-address imas-card__meta text-base mb-3" data-v-6b3d484b><span class="imas-card__address-line" data-v-6b3d484b><i class="fa fa-map-marker imas-address-marker" aria-hidden="true" data-v-6b3d484b></i><span data-v-6b3d484b>${ssrInterpolate(addressLine.value)}</span></span></p>`);
			_push(ssrRenderComponent(PropertyCardUnitTypesBar_default, { "unit-types": __props.property.unit_types ?? [] }, null, _parent));
			_push(`</div></div>`);
			if (__props.property.youtube_video_url && !isSoldOut.value) _push(ssrRenderComponent(VideoLightbox_default, {
				modelValue: videoLightboxOpen.value,
				"onUpdate:modelValue": ($event) => videoLightboxOpen.value = $event,
				"video-url": __props.property.youtube_video_url,
				"aria-label": videoLightboxAria.value,
				"invalid-message": videoInvalidMessage.value
			}, null, _parent));
			else _push(`<!---->`);
			_push(`</div>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyCard.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var PropertyCard_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-6b3d484b"]]);
//#endregion
//#region resources/js/configureImasVueApp.js
/**
* @param {object} ziggy
*/
function createRouteHelper(ziggy) {
	return (name, params, absolute) => B(name, params, absolute, ziggy);
}
/**
* Register shared front-office Vue plugins and global components.
*
* @param {import('vue').App} app
* @param {{ ssr?: boolean, ziggy?: object }} [options]
*/
function configureImasVueApp(app, { ssr = false, ziggy = null } = {}) {
	app.component("PropertyCard", PropertyCard_default).component("VideoLightbox", VideoLightbox_default);
	if (ziggy && typeof ziggy === "object") {
		globalThis.Ziggy = ziggy;
		const routeFn = createRouteHelper(ziggy);
		if (ssr) globalThis.route = routeFn;
		app.use(M, ziggy);
		app.mixin({ methods: { route: routeFn } });
	} else if (!ssr && typeof route === "function") app.mixin({ methods: { route } });
	if (!ssr) app.use(gsap_default);
	return app;
}
//#endregion
//#region node_modules/laravel-vite-plugin/inertia-helpers/index.js
async function resolvePageComponent(path, pages) {
	for (const p of Array.isArray(path) ? path : [path]) {
		const page = pages[p];
		if (typeof page === "undefined") continue;
		return typeof page === "function" ? page() : page;
	}
	throw new Error(`Page not found: ${path}`);
}
//#endregion
//#region resources/js/resolveInertiaPage.js
/**
* Resolve an Inertia page component (core app + module namespaced pages).
*
* @param {string} name
*/
function resolveInertiaPage(name) {
	const modules = name.split("::");
	if (modules.length > 1) return resolvePageComponent(`../../Modules/${modules[0]}/resources/assets/js/Pages/${modules[1]}.vue`, /* @__PURE__ */ Object.assign({
		"../../Modules/Base/resources/assets/js/Pages/AboutUs.vue": () => import("./assets/AboutUs-BfsPimk2.js"),
		"../../Modules/Base/resources/assets/js/Pages/Index.vue": () => import("./assets/Index-nI-iFQ9k.js"),
		"../../Modules/Cms/resources/assets/js/Pages/Index.vue": () => import("./assets/Index-BYMirxkS.js"),
		"../../Modules/Cms/resources/assets/js/Pages/PageShow.vue": () => import("./assets/PageShow-BB1cCgmh.js"),
		"../../Modules/Cms/resources/assets/js/Pages/Show.vue": () => import("./assets/Show-D28qYINo.js"),
		"../../Modules/Corporate/resources/assets/js/Pages/index.vue": () => import("./assets/Pages-DmYQ7aH7.js"),
		"../../Modules/Property/resources/assets/js/Pages/FavoriteProperties.vue": () => import("./assets/FavoriteProperties-DH7LIYI7.js"),
		"../../Modules/Property/resources/assets/js/Pages/TurkishCitizenship.vue": () => import("./assets/TurkishCitizenship-ybCbx3c7.js"),
		"../../Modules/Property/resources/assets/js/Pages/index.vue": () => import("./assets/Pages-C5cU0pDQ.js"),
		"../../Modules/Property/resources/assets/js/Pages/show.vue": () => import("./assets/show-D-AtN-op.js"),
		"../../Modules/Support/resources/assets/js/Pages/ContactUs.vue": () => import("./assets/ContactUs-CZdCmFzw.js"),
		"../../Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue": () => import("./assets/ForgotPassword-Bw8g7MPT.js"),
		"../../Modules/User/resources/assets/js/Pages/Auth/Login.vue": () => import("./assets/Login-fOB2C32W.js"),
		"../../Modules/User/resources/assets/js/Pages/Auth/Register.vue": () => import("./assets/Register-BxjXFUs6.js"),
		"../../Modules/User/resources/assets/js/Pages/Auth/ResetPassword.vue": () => import("./assets/ResetPassword-DM3Rpa6h.js")
	}));
	return resolvePageComponent(`./Pages/${name}.vue`, /* @__PURE__ */ Object.assign({}));
}
//#endregion
//#region resources/js/ssr.js
createServer((page) => createInertiaApp({
	page,
	render: renderToString,
	resolve: resolveInertiaPage,
	title: (title) => {
		if (!title) return page.props?.appName || "IMas";
		return title;
	},
	setup({ App, props, plugin }) {
		const app = createSSRApp({ render: () => h(App, props) }).use(plugin);
		configureImasVueApp(app, {
			ssr: true,
			ziggy: page.props?.ziggy ?? null
		});
		return app;
	}
}));
//#endregion
export { refreshScrollTrigger as a, localizedLocationName as c, VideoLightbox_default as d, _plugin_vue_export_helper_default as f, useOpenAuthModal as h, prefersReducedMotion as i, propertyLocationLine as l, IMAS_OPEN_AUTH_EVENT as m, unitTypeDisplayParts as n, formatPropertyMoney as o, resolveYoutubeHeroBackgroundSrc as p, createGsapContext as r, propertyStartPrice as s, PropertyCard_default as t, localizedField as u };
