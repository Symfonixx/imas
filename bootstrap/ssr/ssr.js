import { usePage, Link, createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import { computed, watch, ref, onMounted, onBeforeUnmount, useSSRContext, mergeProps, unref, createSSRApp, h as h$1 } from "vue";
import { ssrRenderTeleport, ssrRenderAttr, ssrInterpolate, ssrRenderAttrs, ssrRenderComponent, ssrRenderClass } from "vue/server-renderer";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
function t(t3, e2) {
  for (var n2 = 0; n2 < e2.length; n2++) {
    var r2 = e2[n2];
    r2.enumerable = r2.enumerable || false, r2.configurable = true, "value" in r2 && (r2.writable = true), Object.defineProperty(t3, u(r2.key), r2);
  }
}
function e(e2, n2, r2) {
  return n2 && t(e2.prototype, n2), Object.defineProperty(e2, "prototype", { writable: false }), e2;
}
function n() {
  return n = Object.assign ? Object.assign.bind() : function(t3) {
    for (var e2 = 1; e2 < arguments.length; e2++) {
      var n2 = arguments[e2];
      for (var r2 in n2) ({}).hasOwnProperty.call(n2, r2) && (t3[r2] = n2[r2]);
    }
    return t3;
  }, n.apply(null, arguments);
}
function r(t3) {
  return r = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(t4) {
    return t4.__proto__ || Object.getPrototypeOf(t4);
  }, r(t3);
}
function o() {
  try {
    var t3 = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch (t4) {
  }
  return (o = function() {
    return !!t3;
  })();
}
function i(t3, e2) {
  return i = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(t4, e3) {
    return t4.__proto__ = e3, t4;
  }, i(t3, e2);
}
function u(t3) {
  var e2 = function(t4) {
    if ("object" != typeof t4 || !t4) return t4;
    var e3 = t4[Symbol.toPrimitive];
    if (void 0 !== e3) {
      var n2 = e3.call(t4, "string");
      if ("object" != typeof n2) return n2;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return String(t4);
  }(t3);
  return "symbol" == typeof e2 ? e2 : e2 + "";
}
function f(t3) {
  var e2 = "function" == typeof Map ? /* @__PURE__ */ new Map() : void 0;
  return f = function(t4) {
    if (null === t4 || !function(t5) {
      try {
        return -1 !== Function.toString.call(t5).indexOf("[native code]");
      } catch (e3) {
        return "function" == typeof t5;
      }
    }(t4)) return t4;
    if ("function" != typeof t4) throw new TypeError("Super expression must either be null or a function");
    if (void 0 !== e2) {
      if (e2.has(t4)) return e2.get(t4);
      e2.set(t4, n2);
    }
    function n2() {
      return function(t5, e3, n3) {
        if (o()) return Reflect.construct.apply(null, arguments);
        var r2 = [null];
        r2.push.apply(r2, e3);
        var u2 = new (t5.bind.apply(t5, r2))();
        return n3 && i(u2, n3.prototype), u2;
      }(t4, arguments, r(this).constructor);
    }
    return n2.prototype = Object.create(t4.prototype, { constructor: { value: n2, enumerable: false, writable: true, configurable: true } }), i(n2, t4);
  }, f(t3);
}
const c = String.prototype.replace, a = /%20/g, l = { RFC1738: function(t3) {
  return c.call(t3, a, "+");
}, RFC3986: function(t3) {
  return String(t3);
} };
var s = "RFC3986";
const p = Object.prototype.hasOwnProperty, y = Array.isArray, d = /* @__PURE__ */ new WeakMap();
var b = function(t3, e2) {
  return d.set(t3, e2), t3;
};
function v(t3) {
  return d.has(t3);
}
var h = function(t3) {
  return d.get(t3);
}, m = function(t3, e2) {
  d.set(t3, e2);
};
const g = function() {
  const t3 = [];
  for (let e2 = 0; e2 < 256; ++e2) t3.push("%" + ((e2 < 16 ? "0" : "") + e2.toString(16)).toUpperCase());
  return t3;
}(), w = function(t3, e2) {
  const n2 = e2 && e2.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
  for (let e3 = 0; e3 < t3.length; ++e3) void 0 !== t3[e3] && (n2[e3] = t3[e3]);
  return n2;
}, j = function t2(e2, n2, r2) {
  if (!n2) return e2;
  if ("object" != typeof n2) {
    if (y(e2)) e2.push(n2);
    else {
      if (!e2 || "object" != typeof e2) return [e2, n2];
      if (v(e2)) {
        var o2 = h(e2) + 1;
        e2[o2] = n2, m(e2, o2);
      } else (r2 && (r2.plainObjects || r2.allowPrototypes) || !p.call(Object.prototype, n2)) && (e2[n2] = true);
    }
    return e2;
  }
  if (!e2 || "object" != typeof e2) {
    if (v(n2)) {
      for (var i2 = Object.keys(n2), u2 = r2 && r2.plainObjects ? { __proto__: null, 0: e2 } : { 0: e2 }, f2 = 0; f2 < i2.length; f2++) u2[parseInt(i2[f2], 10) + 1] = n2[i2[f2]];
      return b(u2, h(n2) + 1);
    }
    return [e2].concat(n2);
  }
  let c2 = e2;
  return y(e2) && !y(n2) && (c2 = w(e2, r2)), y(e2) && y(n2) ? (n2.forEach(function(n3, o3) {
    if (p.call(e2, o3)) {
      const i3 = e2[o3];
      i3 && "object" == typeof i3 && n3 && "object" == typeof n3 ? e2[o3] = t2(i3, n3, r2) : e2.push(n3);
    } else e2[o3] = n3;
  }), e2) : Object.keys(n2).reduce(function(e3, o3) {
    const i3 = n2[o3];
    return e3[o3] = p.call(e3, o3) ? t2(e3[o3], i3, r2) : i3, e3;
  }, c2);
}, O = 1024, E = function(t3, e2, n2, r2) {
  if (v(t3)) {
    var o2 = h(t3) + 1;
    return t3[o2] = e2, m(t3, o2), t3;
  }
  var i2 = [].concat(t3, e2);
  return i2.length > n2 ? b(w(i2, { plainObjects: r2 }), i2.length - 1) : i2;
}, T = function(t3, e2) {
  if (y(t3)) {
    const n2 = [];
    for (let r2 = 0; r2 < t3.length; r2 += 1) n2.push(e2(t3[r2]));
    return n2;
  }
  return e2(t3);
}, R = Object.prototype.hasOwnProperty, k = { brackets: function(t3) {
  return t3 + "[]";
}, comma: "comma", indices: function(t3, e2) {
  return t3 + "[" + e2 + "]";
}, repeat: function(t3) {
  return t3;
} }, S = Array.isArray, I = Array.prototype.push, A = function(t3, e2) {
  I.apply(t3, S(e2) ? e2 : [e2]);
}, D = Date.prototype.toISOString, $ = { addQueryPrefix: false, allowDots: false, allowEmptyArrays: false, arrayFormat: "indices", charset: "utf-8", charsetSentinel: false, delimiter: "&", encode: true, encodeDotInKeys: false, encoder: function(t3, e2, n2, r2, o2) {
  if (0 === t3.length) return t3;
  let i2 = t3;
  if ("symbol" == typeof t3 ? i2 = Symbol.prototype.toString.call(t3) : "string" != typeof t3 && (i2 = String(t3)), "iso-8859-1" === n2) return escape(i2).replace(/%u[0-9a-f]{4}/gi, function(t4) {
    return "%26%23" + parseInt(t4.slice(2), 16) + "%3B";
  });
  let u2 = "";
  for (let t4 = 0; t4 < i2.length; t4 += O) {
    const e3 = i2.length >= O ? i2.slice(t4, t4 + O) : i2, n3 = [];
    for (let t5 = 0; t5 < e3.length; ++t5) {
      let r3 = e3.charCodeAt(t5);
      45 === r3 || 46 === r3 || 95 === r3 || 126 === r3 || r3 >= 48 && r3 <= 57 || r3 >= 65 && r3 <= 90 || r3 >= 97 && r3 <= 122 || "RFC1738" === o2 && (40 === r3 || 41 === r3) ? n3[n3.length] = e3.charAt(t5) : r3 < 128 ? n3[n3.length] = g[r3] : r3 < 2048 ? n3[n3.length] = g[192 | r3 >> 6] + g[128 | 63 & r3] : r3 < 55296 || r3 >= 57344 ? n3[n3.length] = g[224 | r3 >> 12] + g[128 | r3 >> 6 & 63] + g[128 | 63 & r3] : (t5 += 1, r3 = 65536 + ((1023 & r3) << 10 | 1023 & e3.charCodeAt(t5)), n3[n3.length] = g[240 | r3 >> 18] + g[128 | r3 >> 12 & 63] + g[128 | r3 >> 6 & 63] + g[128 | 63 & r3]);
    }
    u2 += n3.join("");
  }
  return u2;
}, encodeValuesOnly: false, format: s, formatter: l[s], indices: false, serializeDate: function(t3) {
  return D.call(t3);
}, skipNulls: false, strictNullHandling: false }, N = {}, _ = function(t3, e2, n2, r2, o2, i2, u2, f2, c2, a2, l2, s2, p2, y2, d2, b2, v2, h2) {
  let m2 = t3, g2 = h2, w2 = 0, j2 = false;
  for (; void 0 !== (g2 = g2.get(N)) && !j2; ) {
    const e3 = g2.get(t3);
    if (w2 += 1, void 0 !== e3) {
      if (e3 === w2) throw new RangeError("Cyclic object value");
      j2 = true;
    }
    void 0 === g2.get(N) && (w2 = 0);
  }
  if ("function" == typeof a2 ? m2 = a2(e2, m2) : m2 instanceof Date ? m2 = p2(m2) : "comma" === n2 && S(m2) && (m2 = T(m2, function(t4) {
    return t4 instanceof Date ? p2(t4) : t4;
  })), null === m2) {
    if (i2) return c2 && !b2 ? c2(e2, $.encoder, v2, "key", y2) : e2;
    m2 = "";
  }
  if ("string" == typeof (O2 = m2) || "number" == typeof O2 || "boolean" == typeof O2 || "symbol" == typeof O2 || "bigint" == typeof O2 || function(t4) {
    return !(!t4 || "object" != typeof t4 || !(t4.constructor && t4.constructor.isBuffer && t4.constructor.isBuffer(t4)));
  }(m2)) return c2 ? [d2(b2 ? e2 : c2(e2, $.encoder, v2, "key", y2)) + "=" + d2(c2(m2, $.encoder, v2, "value", y2))] : [d2(e2) + "=" + d2(String(m2))];
  var O2;
  const E2 = [];
  if (void 0 === m2) return E2;
  let R2;
  if ("comma" === n2 && S(m2)) b2 && c2 && (m2 = T(m2, c2)), R2 = [{ value: m2.length > 0 ? m2.join(",") || null : void 0 }];
  else if (S(a2)) R2 = a2;
  else {
    const t4 = Object.keys(m2);
    R2 = l2 ? t4.sort(l2) : t4;
  }
  const k2 = f2 ? e2.replace(/\./g, "%2E") : e2, I2 = r2 && S(m2) && 1 === m2.length ? k2 + "[]" : k2;
  if (o2 && S(m2) && 0 === m2.length) return I2 + "[]";
  for (let e3 = 0; e3 < R2.length; ++e3) {
    const g3 = R2[e3], j3 = "object" == typeof g3 && void 0 !== g3.value ? g3.value : m2[g3];
    if (u2 && null === j3) continue;
    const O3 = s2 && f2 ? g3.replace(/\./g, "%2E") : g3, T2 = S(m2) ? "function" == typeof n2 ? n2(I2, O3) : I2 : I2 + (s2 ? "." + O3 : "[" + O3 + "]");
    h2.set(t3, w2);
    const k3 = /* @__PURE__ */ new WeakMap();
    k3.set(N, h2), A(E2, _(j3, T2, n2, r2, o2, i2, u2, f2, "comma" === n2 && b2 && S(m2) ? null : c2, a2, l2, s2, p2, y2, d2, b2, v2, k3));
  }
  return E2;
}, x = Object.prototype.hasOwnProperty, C = Array.isArray, P = { allowDots: false, allowEmptyArrays: false, allowPrototypes: false, allowSparse: false, arrayLimit: 20, charset: "utf-8", charsetSentinel: false, comma: false, decodeDotInKeys: false, decoder: function(t3, e2, n2) {
  const r2 = t3.replace(/\+/g, " ");
  if ("iso-8859-1" === n2) return r2.replace(/%[0-9a-f]{2}/gi, unescape);
  try {
    return decodeURIComponent(r2);
  } catch (t4) {
    return r2;
  }
}, delimiter: "&", depth: 5, duplicates: "combine", ignoreQueryPrefix: false, interpretNumericEntities: false, parameterLimit: 1e3, parseArrays: true, plainObjects: false, strictNullHandling: false }, Z = function(t3) {
  return t3.replace(/&#(\d+);/g, function(t4, e2) {
    return String.fromCharCode(parseInt(e2, 10));
  });
}, F = function(t3, e2) {
  return t3 && "string" == typeof t3 && e2.comma && t3.indexOf(",") > -1 ? t3.split(",") : t3;
}, U = function(t3, e2, n2, r2) {
  if (!t3) return;
  const o2 = n2.allowDots ? t3.replace(/\.([^.[]+)/g, "[$1]") : t3, i2 = /(\[[^[\]]*])/g;
  let u2 = n2.depth > 0 && /(\[[^[\]]*])/.exec(o2);
  const f2 = u2 ? o2.slice(0, u2.index) : o2, c2 = [];
  if (f2) {
    if (!n2.plainObjects && x.call(Object.prototype, f2) && !n2.allowPrototypes) return;
    c2.push(f2);
  }
  let a2 = 0;
  for (; n2.depth > 0 && null !== (u2 = i2.exec(o2)) && a2 < n2.depth; ) {
    if (a2 += 1, !n2.plainObjects && x.call(Object.prototype, u2[1].slice(1, -1)) && !n2.allowPrototypes) return;
    c2.push(u2[1]);
  }
  return u2 && c2.push("[" + o2.slice(u2.index) + "]"), function(t4, e3, n3, r3) {
    let o3 = r3 ? e3 : F(e3, n3);
    for (let e4 = t4.length - 1; e4 >= 0; --e4) {
      let r4;
      const i3 = t4[e4];
      if ("[]" === i3 && n3.parseArrays) r4 = v(o3) ? o3 : n3.allowEmptyArrays && ("" === o3 || n3.strictNullHandling && null === o3) ? [] : E([], o3, n3.arrayLimit, n3.plainObjects);
      else {
        r4 = n3.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
        const t5 = "[" === i3.charAt(0) && "]" === i3.charAt(i3.length - 1) ? i3.slice(1, -1) : i3, e5 = n3.decodeDotInKeys ? t5.replace(/%2E/g, ".") : t5, u3 = parseInt(e5, 10);
        n3.parseArrays || "" !== e5 ? !isNaN(u3) && i3 !== e5 && String(u3) === e5 && u3 >= 0 && n3.parseArrays && u3 <= n3.arrayLimit ? (r4 = [], r4[u3] = o3) : "__proto__" !== e5 && (r4[e5] = o3) : r4 = { 0: o3 };
      }
      o3 = r4;
    }
    return o3;
  }(c2, e2, n2, r2);
};
function q(t3, e2) {
  const n2 = /* @__PURE__ */ function(t4) {
    return P;
  }();
  if ("" === t3 || null == t3) return n2.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
  const r2 = "string" == typeof t3 ? function(t4, e3) {
    const n3 = { __proto__: null }, r3 = (e3.ignoreQueryPrefix ? t4.replace(/^\?/, "") : t4).split(e3.delimiter, Infinity === e3.parameterLimit ? void 0 : e3.parameterLimit);
    let o3, i3 = -1, u2 = e3.charset;
    if (e3.charsetSentinel) for (o3 = 0; o3 < r3.length; ++o3) 0 === r3[o3].indexOf("utf8=") && ("utf8=%E2%9C%93" === r3[o3] ? u2 = "utf-8" : "utf8=%26%2310003%3B" === r3[o3] && (u2 = "iso-8859-1"), i3 = o3, o3 = r3.length);
    for (o3 = 0; o3 < r3.length; ++o3) {
      if (o3 === i3) continue;
      const t5 = r3[o3], f2 = t5.indexOf("]="), c2 = -1 === f2 ? t5.indexOf("=") : f2 + 1;
      let a2, l2;
      -1 === c2 ? (a2 = e3.decoder(t5, P.decoder, u2, "key"), l2 = e3.strictNullHandling ? null : "") : (a2 = e3.decoder(t5.slice(0, c2), P.decoder, u2, "key"), l2 = T(F(t5.slice(c2 + 1), e3), function(t6) {
        return e3.decoder(t6, P.decoder, u2, "value");
      })), l2 && e3.interpretNumericEntities && "iso-8859-1" === u2 && (l2 = Z(l2)), t5.indexOf("[]=") > -1 && (l2 = C(l2) ? [l2] : l2);
      const s2 = x.call(n3, a2);
      s2 && "combine" === e3.duplicates ? n3[a2] = E(n3[a2], l2, e3.arrayLimit, e3.plainObjects) : s2 && "last" !== e3.duplicates || (n3[a2] = l2);
    }
    return n3;
  }(t3, n2) : t3;
  let o2 = n2.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
  const i2 = Object.keys(r2);
  for (let e3 = 0; e3 < i2.length; ++e3) {
    const u2 = i2[e3], f2 = U(u2, r2[u2], n2, "string" == typeof t3);
    o2 = j(o2, f2, n2);
  }
  return true === n2.allowSparse ? o2 : function(t4) {
    const e3 = [{ obj: { o: t4 }, prop: "o" }], n3 = [];
    for (let t5 = 0; t5 < e3.length; ++t5) {
      const r3 = e3[t5], o3 = r3.obj[r3.prop], i3 = Object.keys(o3);
      for (let t6 = 0; t6 < i3.length; ++t6) {
        const r4 = i3[t6], u2 = o3[r4];
        "object" == typeof u2 && null !== u2 && -1 === n3.indexOf(u2) && (e3.push({ obj: o3, prop: r4 }), n3.push(u2));
      }
    }
    return function(t5) {
      for (; t5.length > 1; ) {
        const e4 = t5.pop(), n4 = e4.obj[e4.prop];
        if (y(n4)) {
          const t6 = [];
          for (let e5 = 0; e5 < n4.length; ++e5) void 0 !== n4[e5] && t6.push(n4[e5]);
          e4.obj[e4.prop] = t6;
        }
      }
    }(e3), t4;
  }(o2);
}
var K = /* @__PURE__ */ function() {
  function t3(t4, e2, n3) {
    var r2, o2;
    this.name = t4, this.definition = e2, this.bindings = null != (r2 = e2.bindings) ? r2 : {}, this.wheres = null != (o2 = e2.wheres) ? o2 : {}, this.config = n3;
  }
  var n2 = t3.prototype;
  return n2.matchesUrl = function(t4) {
    var e2, n3 = this;
    if (!this.definition.methods.includes("GET")) return false;
    var r2 = this.template.replace(/[.*+$()[\]]/g, "\\$&").replace(/(\/?){([^}?]*)(\??)}/g, function(t5, e3, r3, o3) {
      var i3, u3 = "(?<" + r3 + ">" + ((null == (i3 = n3.wheres[r3]) ? void 0 : i3.replace(/(^\^)|(\$$)/g, "")) || "[^/?]+") + ")";
      return o3 ? "(" + e3 + u3 + ")?" : "" + e3 + u3;
    }).replace(/^\w+:\/\//, ""), o2 = t4.replace(/^\w+:\/\//, "").split("?"), i2 = o2[0], u2 = o2[1], f2 = null != (e2 = new RegExp("^" + r2 + "/?$").exec(i2)) ? e2 : new RegExp("^" + r2 + "/?$").exec(decodeURI(i2));
    if (f2) {
      for (var c2 in f2.groups) f2.groups[c2] = "string" == typeof f2.groups[c2] ? decodeURIComponent(f2.groups[c2]) : f2.groups[c2];
      return { params: f2.groups, query: q(u2) };
    }
    return false;
  }, n2.compile = function(t4) {
    var e2 = this;
    return this.parameterSegments.length ? this.template.replace(/{([^}?]+)(\??)}/g, function(n3, r2, o2) {
      var i2, u2;
      if (!o2 && [null, void 0].includes(t4[r2])) throw new Error("Ziggy error: '" + r2 + "' parameter is required for route '" + e2.name + "'.");
      if (e2.wheres[r2] && !new RegExp("^" + (o2 ? "(" + e2.wheres[r2] + ")?" : e2.wheres[r2]) + "$").test(null != (u2 = t4[r2]) ? u2 : "")) throw new Error("Ziggy error: '" + r2 + "' parameter '" + t4[r2] + "' does not match required format '" + e2.wheres[r2] + "' for route '" + e2.name + "'.");
      return encodeURI(null != (i2 = t4[r2]) ? i2 : "").replace(/%7C/g, "|").replace(/%25/g, "%").replace(/\$/g, "%24");
    }).replace(this.config.absolute ? /(\.[^/]+?)(\/\/)/ : /(^)(\/\/)/, "$1/").replace(/\/+$/, "") : this.template;
  }, e(t3, [{ key: "template", get: function() {
    var t4 = (this.origin + "/" + this.definition.uri).replace(/\/+$/, "");
    return "" === t4 ? "/" : t4;
  } }, { key: "origin", get: function() {
    return this.config.absolute ? this.definition.domain ? "" + this.config.url.match(/^\w+:\/\//)[0] + this.definition.domain + (this.config.port ? ":" + this.config.port : "") : this.config.url : "";
  } }, { key: "parameterSegments", get: function() {
    var t4, e2;
    return null != (t4 = null == (e2 = this.template.match(/{[^}?]+\??}/g)) ? void 0 : e2.map(function(t5) {
      return { name: t5.replace(/{|\??}/g, ""), required: !/\?}$/.test(t5) };
    })) ? t4 : [];
  } }]);
}(), z = /* @__PURE__ */ function(t3) {
  function r2(e2, r3, o3, i2) {
    var u3;
    if (void 0 === o3 && (o3 = true), (u3 = t3.call(this) || this).t = null != i2 ? i2 : "undefined" != typeof Ziggy ? Ziggy : null == globalThis ? void 0 : globalThis.Ziggy, !u3.t && "undefined" != typeof document && document.getElementById("ziggy-routes-json") && (globalThis.Ziggy = JSON.parse(document.getElementById("ziggy-routes-json").textContent), u3.t = globalThis.Ziggy), u3.t = n({}, u3.t, { absolute: o3 }), e2) {
      if (!u3.t.routes[e2]) throw new Error("Ziggy error: route '" + e2 + "' is not in the route list.");
      u3.i = new K(e2, u3.t.routes[e2], u3.t), u3.u = u3.l(r3);
    }
    return u3;
  }
  var o2, u2;
  u2 = t3, (o2 = r2).prototype = Object.create(u2.prototype), o2.prototype.constructor = o2, i(o2, u2);
  var f2 = r2.prototype;
  return f2.toString = function() {
    var t4 = this, e2 = Object.keys(this.u).filter(function(e3) {
      return !t4.i.parameterSegments.some(function(t5) {
        return t5.name === e3;
      });
    }).filter(function(t5) {
      return "_query" !== t5;
    }).reduce(function(e3, r3) {
      var o3;
      return n({}, e3, ((o3 = {})[r3] = t4.u[r3], o3));
    }, {});
    return this.i.compile(this.u) + function(t5, e3) {
      let n2 = t5;
      const r3 = function(t6) {
        if (!t6) return $;
        if (void 0 !== t6.allowEmptyArrays && "boolean" != typeof t6.allowEmptyArrays) throw new TypeError("`allowEmptyArrays` option can only be `true` or `false`, when provided");
        if (void 0 !== t6.encodeDotInKeys && "boolean" != typeof t6.encodeDotInKeys) throw new TypeError("`encodeDotInKeys` option can only be `true` or `false`, when provided");
        if (null != t6.encoder && "function" != typeof t6.encoder) throw new TypeError("Encoder has to be a function.");
        const e4 = t6.charset || $.charset;
        if (void 0 !== t6.charset && "utf-8" !== t6.charset && "iso-8859-1" !== t6.charset) throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");
        let n3 = s;
        if (void 0 !== t6.format) {
          if (!R.call(l, t6.format)) throw new TypeError("Unknown format option provided.");
          n3 = t6.format;
        }
        const r4 = l[n3];
        let o4, i3 = $.filter;
        if (("function" == typeof t6.filter || S(t6.filter)) && (i3 = t6.filter), o4 = t6.arrayFormat in k ? t6.arrayFormat : "indices" in t6 ? t6.indices ? "indices" : "repeat" : $.arrayFormat, "commaRoundTrip" in t6 && "boolean" != typeof t6.commaRoundTrip) throw new TypeError("`commaRoundTrip` must be a boolean, or absent");
        return { addQueryPrefix: "boolean" == typeof t6.addQueryPrefix ? t6.addQueryPrefix : $.addQueryPrefix, allowDots: void 0 === t6.allowDots ? true === t6.encodeDotInKeys || $.allowDots : !!t6.allowDots, allowEmptyArrays: "boolean" == typeof t6.allowEmptyArrays ? !!t6.allowEmptyArrays : $.allowEmptyArrays, arrayFormat: o4, charset: e4, charsetSentinel: "boolean" == typeof t6.charsetSentinel ? t6.charsetSentinel : $.charsetSentinel, commaRoundTrip: t6.commaRoundTrip, delimiter: void 0 === t6.delimiter ? $.delimiter : t6.delimiter, encode: "boolean" == typeof t6.encode ? t6.encode : $.encode, encodeDotInKeys: "boolean" == typeof t6.encodeDotInKeys ? t6.encodeDotInKeys : $.encodeDotInKeys, encoder: "function" == typeof t6.encoder ? t6.encoder : $.encoder, encodeValuesOnly: "boolean" == typeof t6.encodeValuesOnly ? t6.encodeValuesOnly : $.encodeValuesOnly, filter: i3, format: n3, formatter: r4, serializeDate: "function" == typeof t6.serializeDate ? t6.serializeDate : $.serializeDate, skipNulls: "boolean" == typeof t6.skipNulls ? t6.skipNulls : $.skipNulls, sort: "function" == typeof t6.sort ? t6.sort : null, strictNullHandling: "boolean" == typeof t6.strictNullHandling ? t6.strictNullHandling : $.strictNullHandling };
      }(e3);
      let o3, i2;
      "function" == typeof r3.filter ? (i2 = r3.filter, n2 = i2("", n2)) : S(r3.filter) && (i2 = r3.filter, o3 = i2);
      const u3 = [];
      if ("object" != typeof n2 || null === n2) return "";
      const f3 = k[r3.arrayFormat], c2 = "comma" === f3 && r3.commaRoundTrip;
      o3 || (o3 = Object.keys(n2)), r3.sort && o3.sort(r3.sort);
      const a2 = /* @__PURE__ */ new WeakMap();
      for (let t6 = 0; t6 < o3.length; ++t6) {
        const e4 = o3[t6];
        r3.skipNulls && null === n2[e4] || A(u3, _(n2[e4], e4, f3, c2, r3.allowEmptyArrays, r3.strictNullHandling, r3.skipNulls, r3.encodeDotInKeys, r3.encode ? r3.encoder : null, r3.filter, r3.sort, r3.allowDots, r3.serializeDate, r3.format, r3.formatter, r3.encodeValuesOnly, r3.charset, a2));
      }
      const p2 = u3.join(r3.delimiter);
      let y2 = true === r3.addQueryPrefix ? "?" : "";
      return r3.charsetSentinel && (y2 += "iso-8859-1" === r3.charset ? "utf8=%26%2310003%3B&" : "utf8=%E2%9C%93&"), p2.length > 0 ? y2 + p2 : "";
    }(n({}, e2, this.u._query), { addQueryPrefix: true, arrayFormat: "indices", encodeValuesOnly: true, skipNulls: true, encoder: function(t5, e3) {
      return "boolean" == typeof t5 ? Number(t5) : e3(t5);
    } });
  }, f2.p = function(t4) {
    var e2 = this;
    t4 ? this.t.absolute && t4.startsWith("/") && (t4 = this.v().host + t4) : t4 = this.h();
    var r3 = {}, o3 = Object.entries(this.t.routes).find(function(n2) {
      return r3 = new K(n2[0], n2[1], e2.t).matchesUrl(t4);
    }) || [void 0, void 0];
    return n({ name: o3[0] }, r3, { route: o3[1] });
  }, f2.h = function() {
    var t4 = this.v(), e2 = t4.pathname, n2 = t4.search;
    return (this.t.absolute ? t4.host + e2 : e2.replace(this.t.url.replace(/^\w*:\/\/[^/]+/, ""), "").replace(/^\/+/, "/")) + n2;
  }, f2.current = function(t4, e2) {
    var r3 = this.p(), o3 = r3.name, i2 = r3.params, u3 = r3.query, f3 = r3.route;
    if (!t4) return o3;
    var c2 = new RegExp("^" + t4.replace(/\./g, "\\.").replace(/\*/g, ".*") + "$").test(o3);
    if ([null, void 0].includes(e2) || !c2) return c2;
    var a2 = new K(o3, f3, this.t);
    e2 = this.l(e2, a2);
    var l2 = n({}, i2, u3);
    if (Object.values(e2).every(function(t5) {
      return !t5;
    }) && !Object.values(l2).some(function(t5) {
      return void 0 !== t5;
    })) return true;
    var s2 = function(t5, e3) {
      return Object.entries(t5).every(function(t6) {
        var n2 = t6[0], r4 = t6[1];
        return Array.isArray(r4) && Array.isArray(e3[n2]) ? r4.every(function(t7) {
          return e3[n2].includes(t7) || e3[n2].includes(decodeURIComponent(t7));
        }) : "object" == typeof r4 && "object" == typeof e3[n2] && null !== r4 && null !== e3[n2] ? s2(r4, e3[n2]) : e3[n2] == r4 || e3[n2] == decodeURIComponent(r4);
      });
    };
    return s2(e2, l2);
  }, f2.v = function() {
    var t4, e2, n2, r3, o3, i2, u3 = "undefined" != typeof window ? window.location : {}, f3 = u3.host, c2 = u3.pathname, a2 = u3.search;
    return { host: null != (t4 = null == (e2 = this.t.location) ? void 0 : e2.host) ? t4 : void 0 === f3 ? "" : f3, pathname: null != (n2 = null == (r3 = this.t.location) ? void 0 : r3.pathname) ? n2 : void 0 === c2 ? "" : c2, search: null != (o3 = null == (i2 = this.t.location) ? void 0 : i2.search) ? o3 : void 0 === a2 ? "" : a2 };
  }, f2.has = function(t4) {
    return this.t.routes.hasOwnProperty(t4);
  }, f2.l = function(t4, e2) {
    var r3 = this;
    void 0 === t4 && (t4 = {}), void 0 === e2 && (e2 = this.i), null != t4 || (t4 = {}), t4 = ["string", "number"].includes(typeof t4) ? [t4] : t4;
    var o3 = e2.parameterSegments.filter(function(t5) {
      return !r3.t.defaults[t5.name];
    });
    if (Array.isArray(t4)) t4 = t4.reduce(function(t5, e3, r4) {
      var i3, u3;
      return n({}, t5, o3[r4] ? ((i3 = {})[o3[r4].name] = e3, i3) : "object" == typeof e3 ? e3 : ((u3 = {})[e3] = "", u3));
    }, {});
    else if (1 === o3.length && !t4.hasOwnProperty(o3[0].name) && (t4.hasOwnProperty(Object.values(e2.bindings)[0]) || t4.hasOwnProperty("id"))) {
      var i2;
      (i2 = {})[o3[0].name] = t4, t4 = i2;
    }
    return n({}, this.m(e2), this.j(t4, e2));
  }, f2.m = function(t4) {
    var e2 = this;
    return t4.parameterSegments.filter(function(t5) {
      return e2.t.defaults[t5.name];
    }).reduce(function(t5, r3, o3) {
      var i2, u3 = r3.name;
      return n({}, t5, ((i2 = {})[u3] = e2.t.defaults[u3], i2));
    }, {});
  }, f2.j = function(t4, e2) {
    var r3 = e2.bindings, o3 = e2.parameterSegments;
    return Object.entries(t4).reduce(function(t5, e3) {
      var i2, u3, f3 = e3[0], c2 = e3[1];
      if (!c2 || "object" != typeof c2 || Array.isArray(c2) || !o3.some(function(t6) {
        return t6.name === f3;
      })) return n({}, t5, ((u3 = {})[f3] = c2, u3));
      var a2 = c2.hasOwnProperty(r3[f3]) ? r3[f3] : c2.hasOwnProperty("id") ? "id" : void 0;
      if (void 0 === a2) throw new Error("Ziggy error: object passed as '" + f3 + "' parameter is missing route model binding key '" + r3[f3] + "'.");
      return n({}, t5, ((i2 = {})[f3] = c2[a2], i2));
    }, {});
  }, f2.valueOf = function() {
    return this.toString();
  }, e(r2, [{ key: "params", get: function() {
    var t4 = this.p();
    return n({}, t4.params, t4.query);
  } }, { key: "routeParams", get: function() {
    return this.p().params;
  } }, { key: "queryParams", get: function() {
    return this.p().query;
  } }]);
}(/* @__PURE__ */ f(String));
function B(t3, e2, n2, r2) {
  var o2 = new z(t3, e2, n2, r2);
  return t3 ? o2.toString() : o2;
}
var M = { install: function(t3, e2) {
  var n2 = function(t4, n3, r2, o2) {
    return void 0 === o2 && (o2 = e2), B(t4, n3, r2, o2);
  };
  parseInt(t3.version) > 2 ? (t3.config.globalProperties.route = n2, t3.provide("route", n2)) : t3.mixin({ methods: { route: n2 } });
} };
function resolveVideoPlayback(raw) {
  if (typeof raw !== "string") {
    return null;
  }
  const trimmed = raw.trim();
  if (trimmed === "") {
    return null;
  }
  const iframeSrc = extractIframeSrc(trimmed);
  if (iframeSrc) {
    return { type: "iframe", src: normalizeEmbedUrl(iframeSrc) };
  }
  if (isEmbeddableUrl(trimmed)) {
    return { type: "iframe", src: normalizeEmbedUrl(trimmed) };
  }
  const youtubeId = extractYoutubeId(trimmed);
  if (youtubeId) {
    return {
      type: "iframe",
      src: `https://www.youtube.com/embed/${youtubeId}`
    };
  }
  const vimeoId = extractVimeoId(trimmed);
  if (vimeoId) {
    return {
      type: "iframe",
      src: `https://player.vimeo.com/video/${vimeoId}`
    };
  }
  if (/\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(trimmed)) {
    return { type: "file", src: trimmed };
  }
  return null;
}
function resolveYoutubeHeroBackgroundSrc(raw) {
  var _a;
  const playback = resolveVideoPlayback(raw);
  if (!playback || playback.type !== "iframe") {
    return null;
  }
  const embedSrc = playback.src;
  if (!/youtube(-nocookie)?\.com\/embed\//i.test(embedSrc)) {
    return null;
  }
  const videoId = extractYoutubeId(String(raw ?? "")) || extractYoutubeId(embedSrc);
  if (!videoId) {
    return null;
  }
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
    if (typeof window !== "undefined" && ((_a = window.location) == null ? void 0 : _a.origin)) {
      url.searchParams.set("origin", window.location.origin);
    }
    return url.toString();
  } catch {
    return null;
  }
}
function withVideoAutoplay(embedSrc, options = {}) {
  const autoplay = options.autoplay !== false;
  if (!autoplay || typeof embedSrc !== "string" || embedSrc === "") {
    return embedSrc;
  }
  try {
    const url = new URL(embedSrc, window.location.origin);
    url.searchParams.set("autoplay", "1");
    url.searchParams.set("rel", "0");
    if (url.hostname.includes("youtube.com")) {
      url.searchParams.set("modestbranding", "1");
    }
    return url.toString();
  } catch {
    const joiner = embedSrc.includes("?") ? "&" : "?";
    return `${embedSrc}${joiner}autoplay=1`;
  }
}
function extractIframeSrc(value) {
  var _a;
  const match = value.match(/<iframe[^>]+src=["']([^"']+)["']/i);
  return ((_a = match == null ? void 0 : match[1]) == null ? void 0 : _a.trim()) ?? "";
}
function isEmbeddableUrl(value) {
  return /youtube(-nocookie)?\.com\/embed\//i.test(value) || /player\.vimeo\.com\/video\//i.test(value);
}
function extractYoutubeId(value) {
  const patterns = [
    /youtube(?:-nocookie)?\.com\/embed\/([a-zA-Z0-9_-]{11})/i,
    /youtube\.com\/watch\?[^#]*v=([a-zA-Z0-9_-]{11})/i,
    /youtu\.be\/([a-zA-Z0-9_-]{11})/i,
    /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/i
  ];
  for (const pattern of patterns) {
    const match = value.match(pattern);
    if (match == null ? void 0 : match[1]) {
      return match[1];
    }
  }
  return null;
}
function extractVimeoId(value) {
  const match = value.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
  return (match == null ? void 0 : match[1]) ?? null;
}
function normalizeEmbedUrl(src) {
  if (src.startsWith("//")) {
    return `https:${src}`;
  }
  return src;
}
const _export_sfc = (sfc, props) => {
  const target = sfc.__vccOpts || sfc;
  for (const [key, val] of props) {
    target[key] = val;
  }
  return target;
};
const _sfc_main$2 = {
  __name: "VideoLightbox",
  __ssrInlineRender: true,
  props: {
    modelValue: { type: Boolean, default: false },
    videoUrl: { type: String, default: "" },
    ariaLabel: { type: String, default: "Video player" },
    invalidMessage: { type: String, default: "Video is not available." }
  },
  emits: ["update:modelValue"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const page = usePage();
    const closeLabel = computed(
      () => {
        var _a, _b;
        return ((_a = page.props.translations) == null ? void 0 : _a.Close) || ((_b = page.props.translations) == null ? void 0 : _b.close) || "Close";
      }
    );
    const playback = computed(() => resolveVideoPlayback(props.videoUrl));
    const activeSrc = computed(() => {
      if (!props.modelValue || !playback.value) {
        return "";
      }
      if (playback.value.type === "iframe") {
        return withVideoAutoplay(playback.value.src);
      }
      return playback.value.src;
    });
    function lockBodyScroll(lock) {
      if (typeof document === "undefined") {
        return;
      }
      document.body.style.overflow = lock ? "hidden" : "";
    }
    watch(
      () => props.modelValue,
      (open) => {
        lockBodyScroll(open);
      }
    );
    const mounted = ref(false);
    onMounted(() => {
      mounted.value = true;
    });
    onBeforeUnmount(() => {
      lockBodyScroll(false);
    });
    return (_ctx, _push, _parent, _attrs) => {
      if (mounted.value) {
        ssrRenderTeleport(_push, (_push2) => {
          var _a, _b;
          if (__props.modelValue) {
            _push2(`<div class="imas-video-lightbox" role="dialog" aria-modal="true"${ssrRenderAttr("aria-label", __props.ariaLabel)} data-v-75f8f886><button type="button" class="imas-video-lightbox__backdrop"${ssrRenderAttr("aria-label", closeLabel.value)} data-v-75f8f886></button><div class="imas-video-lightbox__dialog" data-v-75f8f886><button type="button" class="imas-video-lightbox__close"${ssrRenderAttr("aria-label", closeLabel.value)} data-v-75f8f886><i class="fa fa-times" aria-hidden="true" data-v-75f8f886></i></button><div class="imas-video-lightbox__content" data-v-75f8f886>`);
            if (((_a = playback.value) == null ? void 0 : _a.type) === "iframe" && activeSrc.value) {
              _push2(`<iframe${ssrRenderAttr("src", activeSrc.value)} class="imas-video-lightbox__iframe"${ssrRenderAttr("title", __props.ariaLabel)} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" data-v-75f8f886></iframe>`);
            } else if (((_b = playback.value) == null ? void 0 : _b.type) === "file" && activeSrc.value) {
              _push2(`<video class="imas-video-lightbox__video"${ssrRenderAttr("src", activeSrc.value)} controls playsinline data-v-75f8f886></video>`);
            } else {
              _push2(`<p class="imas-video-lightbox__error text-center mb-0" data-v-75f8f886>${ssrInterpolate(__props.invalidMessage)}</p>`);
            }
            _push2(`</div></div></div>`);
          } else {
            _push2(`<!---->`);
          }
        }, "body", false, _parent);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Global/VideoLightbox.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const VideoLightbox = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["__scopeId", "data-v-75f8f886"]]);
function localizedField(value, locale = "en") {
  if (typeof value === "string") {
    return value.trim();
  }
  if (value && typeof value === "object") {
    const raw = value[locale] ?? value.en ?? Object.values(value).find((v2) => typeof v2 === "string");
    return typeof raw === "string" ? raw.trim() : "";
  }
  return "";
}
function localizedLocationName(name, locale = "en") {
  if (typeof name === "string") {
    return name.trim();
  }
  if (name && typeof name === "object") {
    const value = name[locale] ?? name.en ?? Object.values(name)[0];
    return typeof value === "string" ? value.trim() : "";
  }
  return "";
}
function propertyLocationLine(location, locale = "en") {
  var _a, _b, _c;
  if (!location) {
    return "";
  }
  const parts = [
    localizedLocationName((_a = location.city) == null ? void 0 : _a.name, locale),
    localizedLocationName((_b = location.district) == null ? void 0 : _b.name, locale),
    localizedLocationName((_c = location.area) == null ? void 0 : _c.name, locale)
  ].filter(Boolean);
  return parts.join(", ");
}
function propertyStartPrice(property) {
  const start = Number(property == null ? void 0 : property.start_price);
  if (Number.isFinite(start)) {
    return start;
  }
  const fallback = Number(property == null ? void 0 : property.price);
  return Number.isFinite(fallback) ? fallback : null;
}
function formatPropertyMoney(amount, locale = "en") {
  const n2 = Number(amount);
  if (!Number.isFinite(n2)) {
    return "—";
  }
  const formatted = new Intl.NumberFormat(locale, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(n2);
  return `$${formatted}`;
}
gsap.registerPlugin(ScrollTrigger);
gsap.defaults({
  ease: "power2.out",
  duration: 1
});
function prefersReducedMotion() {
  var _a, _b;
  return typeof window !== "undefined" && ((_b = (_a = window.matchMedia) == null ? void 0 : _a.call(window, "(prefers-reduced-motion: reduce)")) == null ? void 0 : _b.matches) === true;
}
function createGsapContext(fn, scope) {
  if (prefersReducedMotion()) {
    return { revert() {
    } };
  }
  return gsap.context(fn, scope ?? void 0);
}
function refreshScrollTrigger() {
  if (prefersReducedMotion()) {
    return;
  }
  requestAnimationFrame(() => {
    ScrollTrigger.refresh();
  });
}
const gsapPlugin = {
  install(app) {
    app.config.globalProperties.$gsap = gsap;
    app.config.globalProperties.$ScrollTrigger = ScrollTrigger;
    app.provide("gsap", gsap);
    app.provide("ScrollTrigger", ScrollTrigger);
  }
};
function formatAreaNumber(value) {
  const n2 = Number(value);
  if (!Number.isFinite(n2)) {
    return null;
  }
  return new Intl.NumberFormat(void 0, {
    maximumFractionDigits: 0
  }).format(n2);
}
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
function unitTypeDisplayParts(unitType) {
  if (!unitType) {
    return { name: "—", area: "" };
  }
  const name = String(unitType.name ?? "").trim() || "—";
  const area = unitTypeAreaRange(unitType.min_area, unitType.max_area);
  return { name, area };
}
const _sfc_main$1 = {
  __name: "PropertyCardUnitTypesBar",
  __ssrInlineRender: true,
  props: {
    unitTypes: {
      type: Array,
      default: () => []
    }
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    const trans = (key) => page.props.translations[key] || key;
    const activeIndex = ref(0);
    let rotateTimer = null;
    const activeUnit = computed(
      () => unitTypeDisplayParts(props.unitTypes[activeIndex.value])
    );
    const countLabel = computed(() => {
      const n2 = props.unitTypes.length;
      if (n2 === 1) {
        return trans("properties.unit_types_count_one");
      }
      const template = trans("properties.unit_types_count");
      return template.includes(":count") ? template.replace(":count", String(n2)) : `${n2} ${template}`;
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
      if (props.unitTypes.length <= 1 || prefersReducedMotion()) {
        return;
      }
      rotateTimer = setInterval(() => {
        activeIndex.value = (activeIndex.value + 1) % props.unitTypes.length;
      }, 3e3);
    }
    watch(
      () => props.unitTypes,
      () => startRotateTimer(),
      { deep: true }
    );
    onMounted(() => startRotateTimer());
    onBeforeUnmount(() => clearRotateTimer());
    return (_ctx, _push, _parent, _attrs) => {
      if (__props.unitTypes.length > 0) {
        _push(`<div${ssrRenderAttrs(mergeProps({
          class: "imas-unit-types-bar text-base pb-3",
          "aria-label": trans("properties.unit_types_aria")
        }, _attrs))} data-v-20df4a12><div class="imas-unit-types-bar__left" data-v-20df4a12><i class="fa fa-building imas-unit-types-bar__icon" aria-hidden="true" data-v-20df4a12></i><div class="imas-unit-types-flip" aria-live="polite" data-v-20df4a12><div class="imas-unit-types-flip__slide" data-v-20df4a12><span class="imas-unit-types-flip__name" data-v-20df4a12>${ssrInterpolate(activeUnit.value.name)}</span>`);
        if (activeUnit.value.area) {
          _push(`<span class="imas-unit-types-flip__sep" aria-hidden="true" data-v-20df4a12>→</span>`);
        } else {
          _push(`<!---->`);
        }
        if (activeUnit.value.area) {
          _push(`<span class="imas-unit-types-flip__area" dir="ltr" data-v-20df4a12>${ssrInterpolate(activeUnit.value.area)}</span>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div></div></div><span class="imas-unit-types-bar__count" data-v-20df4a12><i class="fa fa-circle imas-unit-types-bar__dot" aria-hidden="true" data-v-20df4a12></i> ${ssrInterpolate(countLabel.value)}</span></div>`);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyCardUnitTypesBar.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const PropertyCardUnitTypesBar = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["__scopeId", "data-v-20df4a12"]]);
const _sfc_main = {
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
    const trans = (key) => page.props.translations[key] || key;
    const locale = computed(() => page.props.locale || "en");
    computed(() => page.props.auth != null);
    const isSoldOut = computed(() => Boolean(props.property.is_sold_out));
    const localFavorited = ref(Boolean(props.property.is_favorited));
    const videoLightboxOpen = ref(false);
    watch(
      () => props.property.is_favorited,
      (v2) => {
        localFavorited.value = Boolean(v2);
      }
    );
    const favoriteAriaLabel = computed(
      () => localFavorited.value ? trans("properties.remove_favorite") : trans("properties.add_favorite")
    );
    const propertyTypeLabel = computed(() => {
      const type = props.property.property_type;
      if (!type) {
        return "";
      }
      return localizedField(type.name, locale.value);
    });
    const displayTitle = computed(() => {
      const t3 = props.property.title;
      return typeof t3 === "string" && t3.trim() !== "" ? t3 : props.property.project_name || props.property.project_code || "Property";
    });
    const soldOutCardLabel = computed(
      () => `${displayTitle.value} – ${trans("properties.sold_out")}`
    );
    const showUrl = computed(() => {
      var _a, _b;
      if (typeof props.property.url === "string" && props.property.url.trim() !== "") {
        return props.property.url;
      }
      try {
        if (typeof route === "function" && ((_b = (_a = route()).has) == null ? void 0 : _b.call(_a, "property.show"))) {
          const slug2 = props.property.url_key || props.property.slug || props.property.project_code;
          if (slug2) {
            return route("property.show", slug2);
          }
        }
      } catch {
      }
      const slug = props.property.url_key || props.property.slug || props.property.project_code;
      return slug ? `/property/${encodeURIComponent(slug)}` : "#";
    });
    const addressLine = computed(() => {
      const line = propertyLocationLine(props.property.location, locale.value);
      return line !== "" ? line : "—";
    });
    function stripHtml(value) {
      if (typeof value !== "string") {
        return "";
      }
      return value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    }
    const overviewText = computed(
      () => stripHtml(localizedLocationName(props.property.overview, locale.value))
    );
    function formatMoney(amount) {
      return formatPropertyMoney(amount, locale.value);
    }
    const priceAmount = computed(
      () => formatMoney(propertyStartPrice(props.property))
    );
    const playVideoLabel = computed(() => trans("property_show.play_video"));
    const videoInvalidMessage = computed(
      () => trans("property_show.video_unavailable")
    );
    const videoLightboxAria = computed(
      () => `${playVideoLabel.value} – ${displayTitle.value}`
    );
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({
        class: ["imas-property-card imas-property-card--media-overlay item user-select-none", [__props.columnClass, { "imas-property-card--sold-out": isSoldOut.value }]],
        "aria-disabled": isSoldOut.value ? "true" : void 0
      }, _attrs))} data-v-86dd8d01><div class="project-single imas-card__surface" data-v-86dd8d01>`);
      if (!isSoldOut.value) {
        _push(ssrRenderComponent(unref(Link), {
          href: showUrl.value,
          class: "imas-property-card__stretched-link",
          "aria-label": displayTitle.value
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="project-inner project-head imas-card__media" data-v-86dd8d01><div class="homes" data-v-86dd8d01><div class="homes-img"${ssrRenderAttr("aria-label", isSoldOut.value ? soldOutCardLabel.value : void 0)} data-v-86dd8d01>`);
      if (propertyTypeLabel.value || __props.property.is_featured) {
        _push(`<div class="homes-tag button alt imas-badge--type" data-v-86dd8d01>`);
        if (__props.property.is_featured) {
          _push(`<i class="fa fa-star imas-featured-star"${ssrRenderAttr("aria-label", trans("properties.featured"))} data-v-86dd8d01></i>`);
        } else {
          _push(`<!---->`);
        }
        if (propertyTypeLabel.value) {
          _push(`<span data-v-86dd8d01>${ssrInterpolate(propertyTypeLabel.value)}</span>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      if (__props.property.is_sold_out) {
        _push(`<div class="homes-tag button alt imas-sold-out-badge imas-badge--danger" data-v-86dd8d01>${ssrInterpolate(trans("properties.sold_out"))}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<img${ssrRenderAttr("src", __props.property.thumbnail_url)}${ssrRenderAttr("alt", __props.property.thumbnail_alt || displayTitle.value)}${ssrRenderAttr("title", __props.property.thumbnail_title || void 0)} class="img-responsive" data-v-86dd8d01></div></div><div class="imas-card-actions" data-v-86dd8d01><div class="homes-price imas-start-price imas-chip" data-v-86dd8d01><span class="imas-start-price__from" data-v-86dd8d01>${ssrInterpolate(trans("properties.price_from"))}</span><span class="imas-start-price__amount" data-v-86dd8d01>${ssrInterpolate(priceAmount.value)}</span></div>`);
      if (!isSoldOut.value) {
        _push(`<div class="button-effect" data-v-86dd8d01>`);
        if (__props.property.youtube_video_url) {
          _push(`<button type="button" class="btn imas-card-video-btn"${ssrRenderAttr("aria-label", playVideoLabel.value)} data-v-86dd8d01><i class="fas fa-video" aria-hidden="true" data-v-86dd8d01></i></button>`);
        } else {
          _push(`<!---->`);
        }
        _push(`<button type="button" class="${ssrRenderClass([{ "is-favorited": localFavorited.value }, "btn imas-favorite-btn"])}"${ssrRenderAttr("aria-label", favoriteAriaLabel.value)}${ssrRenderAttr("aria-pressed", localFavorited.value)} data-v-86dd8d01><i class="${ssrRenderClass([
          localFavorited.value ? "fa-heart" : "fa-heart-o",
          "fa favorite-icon"
        ])}" aria-hidden="true" data-v-86dd8d01></i></button></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div class="homes-content imas-card__body" data-v-86dd8d01><h3 class="imas-property-title imas-card__title" data-v-86dd8d01><span class="imas-card__title-text" data-v-86dd8d01>${ssrInterpolate(displayTitle.value)}</span></h3>`);
      if (overviewText.value) {
        _push(`<p class="imas-property-overview imas-card__excerpt text-card-excerpt mb-3" data-v-86dd8d01>${ssrInterpolate(overviewText.value)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<p class="homes-address imas-card__meta text-base mb-3" data-v-86dd8d01><span class="imas-card__address-line" data-v-86dd8d01><i class="fa fa-map-marker imas-address-marker" aria-hidden="true" data-v-86dd8d01></i><span data-v-86dd8d01>${ssrInterpolate(addressLine.value)}</span></span></p>`);
      _push(ssrRenderComponent(PropertyCardUnitTypesBar, {
        "unit-types": __props.property.unit_types ?? []
      }, null, _parent));
      _push(`</div></div>`);
      if (__props.property.youtube_video_url && !isSoldOut.value) {
        _push(ssrRenderComponent(VideoLightbox, {
          modelValue: videoLightboxOpen.value,
          "onUpdate:modelValue": ($event) => videoLightboxOpen.value = $event,
          "video-url": __props.property.youtube_video_url,
          "aria-label": videoLightboxAria.value,
          "invalid-message": videoInvalidMessage.value
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyCard.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const PropertyCard = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-86dd8d01"]]);
function createRouteHelper(ziggy) {
  return (name, params, absolute) => B(name, params, absolute, ziggy);
}
function configureImasVueApp(app, { ssr = false, ziggy = null } = {}) {
  app.component("PropertyCard", PropertyCard).component(
    "VideoLightbox",
    VideoLightbox
  );
  if (ziggy && typeof ziggy === "object") {
    globalThis.Ziggy = ziggy;
    const routeFn = createRouteHelper(ziggy);
    if (ssr) {
      globalThis.route = routeFn;
    }
    app.use(M, ziggy);
    app.mixin({ methods: { route: routeFn } });
  } else if (!ssr && typeof route === "function") {
    app.mixin({ methods: { route } });
  }
  if (!ssr) {
    app.use(gsapPlugin);
  }
  return app;
}
async function resolvePageComponent(path, pages) {
  for (const p2 of Array.isArray(path) ? path : [path]) {
    const page = pages[p2];
    if (typeof page === "undefined") {
      continue;
    }
    return typeof page === "function" ? page() : page;
  }
  throw new Error(`Page not found: ${path}`);
}
function resolveInertiaPage(name) {
  const modules = name.split("::");
  if (modules.length > 1) {
    return resolvePageComponent(
      `../../Modules/${modules[0]}/resources/assets/js/Pages/${modules[1]}.vue`,
      /* @__PURE__ */ Object.assign({ "../../Modules/Base/resources/assets/js/Pages/AboutUs.vue": () => import("./assets/AboutUs-eEkKjWs-.js"), "../../Modules/Base/resources/assets/js/Pages/Index.vue": () => import("./assets/Index-CVBAYdp8.js"), "../../Modules/Cms/resources/assets/js/Pages/Index.vue": () => import("./assets/Index-CBnhmw_m.js"), "../../Modules/Cms/resources/assets/js/Pages/PageShow.vue": () => import("./assets/PageShow-Bs5jQfi7.js"), "../../Modules/Cms/resources/assets/js/Pages/Show.vue": () => import("./assets/Show-D9Ht3m7s.js"), "../../Modules/Corporate/resources/assets/js/Pages/index.vue": () => import("./assets/index-CpT6CBO6.js"), "../../Modules/Property/resources/assets/js/Pages/FavoriteProperties.vue": () => import("./assets/FavoriteProperties-CgpfosEi.js"), "../../Modules/Property/resources/assets/js/Pages/TurkishCitizenship.vue": () => import("./assets/TurkishCitizenship-CU7enzhV.js"), "../../Modules/Property/resources/assets/js/Pages/index.vue": () => import("./assets/index-CSqUDyTl.js"), "../../Modules/Property/resources/assets/js/Pages/show.vue": () => import("./assets/show-DpmTIvkc.js"), "../../Modules/Support/resources/assets/js/Pages/ContactUs.vue": () => import("./assets/ContactUs-DqUsJ3mt.js"), "../../Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue": () => import("./assets/ForgotPassword-B3SwHwVi.js"), "../../Modules/User/resources/assets/js/Pages/Auth/Login.vue": () => import("./assets/Login-CHGRPpE1.js"), "../../Modules/User/resources/assets/js/Pages/Auth/Register.vue": () => import("./assets/Register-CZ9scLu-.js"), "../../Modules/User/resources/assets/js/Pages/Auth/ResetPassword.vue": () => import("./assets/ResetPassword-jRLxes5m.js") })
    );
  }
  return resolvePageComponent(
    `./Pages/${name}.vue`,
    /* @__PURE__ */ Object.assign({})
  );
}
createServer(
  (page) => createInertiaApp({
    page,
    render: renderToString,
    resolve: resolveInertiaPage,
    setup({ App, props, plugin }) {
      var _a;
      const app = createSSRApp({ render: () => h$1(App, props) }).use(
        plugin
      );
      configureImasVueApp(app, {
        ssr: true,
        ziggy: ((_a = page.props) == null ? void 0 : _a.ziggy) ?? null
      });
      return app;
    }
  })
);
export {
  PropertyCard as P,
  VideoLightbox as V,
  _export_sfc as _,
  propertyStartPrice as a,
  propertyLocationLine as b,
  createGsapContext as c,
  localizedLocationName as d,
  resolveYoutubeHeroBackgroundSrc as e,
  formatPropertyMoney as f,
  localizedField as l,
  prefersReducedMotion as p,
  refreshScrollTrigger as r
};
