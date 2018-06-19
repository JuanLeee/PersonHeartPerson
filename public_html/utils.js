function min(a, b)
{
	if (a === undefined)
		return b;
	if (b === undefined)
		return a;
	return a < b ? a : b;
}
function max(a, b)
{
	if (a === undefined)
		return b;
	if (b === undefined)
		return a;
	return a > b ? a : b;
}
function abs(x)
{
	return x < 0 ? -x : x;
}
function sgn(x)
{
	if (x < 0)
		return -1;
	if (x > 0)
		return 1;
	return 0;
}
function mod(a, M)
{
	a %= M;
	if (a < 0)
		a += M;
	return a;
}
function sqr(x)
{
	return x * x;
}

function normalizeAngle(a)
{
	return mod(a, Math.PI * 2);
}

function vadd(a, b)
{
	return [a[0] + b[0], a[1] + b[1]];
}
function vsub(a, b)
{
	return [a[0] - b[0], a[1] - b[1]];
}
function vmul(a, s)
{
	return [a[0] * s, a[1] * s];
}
function vdiv(a, s)
{
	return [a[0] / s, a[1] / s];
}
function vcopy(a)
{
	return [a[0], a[1]];
}
function vnorm2(a)
{
	return a[0] * a[0] + a[1] * a[1];
}
function vnorm(a)
{
	return Math.sqrt(vnorm2(a));
}
function vdist(a, b)
{
	return vnorm(vsub(a, b));
}
function vunit(a)
{
	var x = vnorm(a);
	if (x == 0) return [0, 0];
	return vdiv(a, x);
}
function vscale(a, m)
{
	return vmul(vunit(a), m);
}
function vclamp(a, m)
{
	return vnorm(a) > m ? vscale(a, m) : vcopy(a);
}
function vresist(a, m)
{
	return vnorm(a) < m ? [0, 0] : vsub(a, vscale(a, m));
}
function vdot(a, b)
{
	return a[0] * b[0] + a[1] * b[1];
}
function vcross(a, b)
{
	return a[0] * b[1] - a[1] * b[0];
}
function vcrossPts(a, b, c, d)
{
	return vcross(vsub(b, a), vsub(d, c));
}
function vdotPts(a, b, c, d)
{
	return vdot(vsub(b, a), vsub(d, c));
}
function vatan(a)
{
	return Math.atan2(a[1], a[0]);
}
function vsincos(angle)
{
	return [Math.cos(angle), Math.sin(angle)];
}
function vrot90(v, iter)
{
	if (iter === undefined)
		iter = 1;
	iter %= 4;
	if (iter < 0)
		iter += 4;
	for (var i = 0; i < iter; i++)
		v = [-v[1], v[0]];
	return v;
}
function vreflect(v, n)
{
	return vsub(v, vmul(n, 2 * vdot(v, n)));
}
function veq(a, b)
{
	return a[0] == b[0] && a[1] == b[1];
}

function randomCirclePoint(r)
{
	var p;
	do {
		p = [random1() * r, random1() * r];
	} while (vnorm(p) > r);
	return p;
}

function random1()
{
	return Math.random() * 2 - 1;
}

function randomFrom(arr)
{
	return arr[Math.floor(Math.random() * arr.length)];
}

function randomColor()
{
	var a = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];
	return "#" + randomFrom(a) + randomFrom(a) + randomFrom(a) + randomFrom(a) + randomFrom(a) + randomFrom(a);
}

function randomUnitVector()
{
	return vunit([Math.random() - 0.5, Math.random() - 0.5]);
}


function segmentsIntersect(a, b, c, d)
{
	return sgn(vcrossPts(a, b, a, c)) != sgn(vcrossPts(a, b, a, d)) && sgn(vcrossPts(c, d, c, a)) != sgn(vcrossPts(c, d, c, b));
}

function makeLine(a, b)
{
	var A = a[1] - b[1];
	var B = b[0] - a[0];
	var C = A * a[0] + B * a[1];
	return {
		A: A,
		B: B,
		C: C
	};
}

function lineIntersection(a, b)
{
	return [
		(a.C * b.B - a.B * b.C) / (a.A * b.B - b.A * a.B),
		(a.C * b.A - a.A * b.C) / (a.B * b.A - a.A * b.B)];
}

function intersection(a1, a2, b1, b2)
{
	if (!segmentsIntersect(a1, a2, b1, b2))
		return null;

	return lineIntersection(makeLine(a1, a2), makeLine(b1, b2));
}

function pointLineDist(p, A, B)
{
	return abs(vcrossPts(A, B, A, p) / vnorm(vsub(B, A)));
}

function pointLineSegmentDist(p, A, B)
{
	if (vdotPts(A, B, A, p) > 0 && vdotPts(B, A, B, p) > 0)
		return pointLineDist(p, A, B);
	else
		return min(vdist(A, p), vdist(B, p));
}

function assert(x, msg)
{
	if (!x)
	{
		if (msg)
			throw "Assertion failed: " + msg;
		else
			throw "Assertion failed [no message]";
	}
}

function extractColorComponents(s)
{
	assert(s.length == 7 && s[0] == '#', "Color in invalid format: <" + s + ">");
	return [
		parseInt(s.substring(1, 3), 16),
		parseInt(s.substring(3, 5), 16),
		parseInt(s.substring(5, 7), 16)
	];
}

function formatNumberTo2DigitHex(x)
{
	var z = "0123456789ABCDEF";
	return z[Math.floor(x / 16)] + z[Math.floor(x % 16)];
}

function formatColorComponents(a)
{
	return "#" +
		formatNumberTo2DigitHex(a[0]) +
		formatNumberTo2DigitHex(a[1]) +
		formatNumberTo2DigitHex(a[2]);
}

function averageColors(a, b, wA, wB)
{
	if (wA === undefined) wA = 1;
	if (wB === undefined) wB = 1;
	var A = extractColorComponents(a);
	var B = extractColorComponents(b);
	return formatColorComponents([
		(A[0] * wA + B[0] * wB) / (wA + wB),
		(A[1] * wA + B[1] * wB) / (wA + wB),
		(A[2] * wA + B[2] * wB) / (wA + wB)
	]);
}