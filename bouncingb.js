var canvas = document.getElementById('canvas');
var gContext;
var canvasWidth, canvasHeight;
var W = 0;
var H = 0;
var first = true;
var particles = [];
var prevTime = undefined;
var kRepel = 1e7;
var kRadius = 2;
var mousePos = [-1e5, -1e5];
var boundingLines = [];
var bannedRect;
var seenMouseMove = false;
var originTime;
var cachedBall;
var cachedNothing;
var cacheSz = 8;

function main()
{
	window.onmousemove = handleMouseMove;
	cachedBall = createCachedCanvas(true);
	cachedNothing = createCachedCanvas(false);
	drawLoop();
}

function createCachedCanvas(drawOn)
{
	var c = document.createElement('canvas');
	c.width = c.height = cacheSz;
	gContext = c.getContext('2d');
	if (drawOn)
		fillCircle([cacheSz / 2, cacheSz / 2], kRadius, "#44aaff");
	return {
		canvas: c,
		offset: [-cacheSz / 2, -cacheSz / 2],
	};
}

function handleMouseMove(event)
{
	if (!seenMouseMove)
		originTime = Date.now();
	seenMouseMove = true;
	mousePos[0] = event.clientX;
	mousePos[1] = event.clientY;
}

function sqr(x)
{
	return x * x;
}

function randomPoint()
{
	return [Math.random() * W, Math.random() * H]
}

function generate()
{
	for (var i = 0; i < 140; i++)
		particles.push(makeParticle());
}

function makeParticle()
{
	return {
		oldPos: [0, 0],
		pos: randomPoint(),
		vel: [0, 0],
	};
}

function handlePhysics(p, dt)
{
	for (var i = 0; inBadPosition(p.pos); i++)
	{
		p.pos = randomPoint();
		if (i == 5) return;
	}
	var dp = vmul(vsub(p.pos, mousePos), 1920 / canvasWidth);
	var r = vnorm2(dp) + 1;
	if (r < sqr(400))
	{
		var intensity = 1.0 / r * kRepel;
		p.vel = vadd(p.vel, vscale(dp, intensity * dt));
	}
	p.vel = vmul(p.vel, Math.pow(0.4, dt));
	var p1 = p.pos;
	var p2 = vadd(p1, vmul(p.vel, dt));
	var pvel = p.vel;
	p.pos = p2;
	if (inBadPosition(p2, kRadius + 1))
	{
		boundingLines.forEach(function(line) {
			if (segmentsIntersect(p1, p2, line[0], line[1]) || pointLineSegmentDist(p2, line[0], line[1]) <= kRadius)
			{
				var n = vunit(vrot90(vsub(line[1], line[0])));
				p.pos = p1;
				p.vel = vadd(pvel, vmul(n, -2 * vdot(n, pvel)));
				p.vel = vmul(p.vel, 0.8);
			}
		});
	}
}

function addBoundingRect(x, y, w, h)
{
	boundingLines.push([[x, y],         [x + w, y]]);
	boundingLines.push([[x + w, y + h], [x + w, y]]);
	boundingLines.push([[x, y],         [x, y + h]]);
	boundingLines.push([[x + w, y + h], [x, y + h]]);
}

function inBadPosition(pt, tol)
{
	if (tol === undefined) tol = kRadius - 1e-4;
	if (pt[0] < 0 + tol || pt[1] < 0 + tol || pt[0] > W - tol || pt[1] > H - tol)
		return true;
	if (bannedRect === undefined)
		return false;
	return true &&
		bannedRect.left < pt[0] + tol &&
		          pt[0] < bannedRect.right + tol &&
		 bannedRect.top < pt[1] + tol &&
		          pt[1] < bannedRect.bottom + tol;
}

function addBodyLines()
{
	var body = document.getElementById("main-container");
	if (!body) return;
	var rect = bannedRect = body.getBoundingClientRect();
	if (!rect) return;
	addBoundingRect(rect.left, rect.top, rect.right - rect.left, rect.bottom - rect.top);
}

function drawLoop()
{
	requestAnimationFrame(drawLoop);
	if (prevTime === undefined)
		prevTime = Date.now();
	var nowTime = Date.now();
	var dt = (nowTime - prevTime) / 1000;
	prevTime = nowTime;
	if (!seenMouseMove)
		return;
	W = window.innerWidth;
	H = window.innerHeight;
	if (W * H < 100)
		return;
	if (particles.length == 0)
		generate();
	var redraw = false;
	if ((first || canvas.width != window.innerWidth || canvas.height != window.innerHeight) && (window.innerWidth * window.innerHeight <= 2560 * 1440))
	{
		canvas.width = window.innerWidth;
		canvas.height = window.innerHeight;
		redraw = true;
	}
	if (nowTime - originTime < 1100)
		redraw = true;
	var w = canvasWidth = canvas.width;
	var h = canvasHeight = canvas.height;
	gContext = canvas.getContext('2d');
	boundingLines = []
	addBoundingRect(0, 0, w, h);
	addBodyLines();
	particles.forEach(function(p) {
		handlePhysics(p, dt);
		if (vnorm2(p.vel) > 9)
			redraw = true;
	});
	if (!redraw)
		return;

	gContext.clearRect(0, 0, canvasWidth, canvasHeight);
	particles.forEach(function(p) {
		gContext.clearRect(p.oldPos[0] - cacheSz / 2, p.oldPos[1] - cacheSz / 2, cacheSz, cacheSz);
	});
	if (originTime !== undefined)
	{
		gContext.globalAlpha = min(1, (nowTime - originTime) / 1000);
		particles.forEach(function(p) {
			if (!inBadPosition(p.pos))
			{
				drawCached(cachedBall, p.pos);
			}
			p.oldPos = p.pos;
		});
		gContext.globalAlpha = 1;
	}

	redraw = first = false;
}

function drawCached(cached, pt, ow)
{
	pt = vadd(pt, cached.offset);
	gContext.drawImage(cached.canvas, pt[0], pt[1]);
}

function fillCircle(p, radius, style)
{
	if (style)
		gContext.fillStyle = style;
	gContext.beginPath();
	gContext.arc(p[0], p[1], radius, 0, Math.PI * 2);
	gContext.fill();
}

main();