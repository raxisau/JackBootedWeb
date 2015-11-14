/*
 * Copyright (c) 2008 John Sutherland <john@sneeu.com> http://sneeu.com/projects/shiftclick/
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

(function(a){a.fn.shiftClick=function(){var b;var c=a(this);this.each(function(){a(this).click(function(a){if(a.shiftKey){var d=c.index(b);var e=c.index(this);var f=Math.min(e,d);var g=Math.max(e,d);var h=b.checked;for(var i=f;i<g;i++){c[i].checked=h}}else{b=this}})})}})(jQuery)