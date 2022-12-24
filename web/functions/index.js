const functions = require("firebase-functions");
const app = require("express")();
const Canvas = require("canvas-prebuilt");
const _ = require("lodash");
const ray = require("./ray");

// Create and deploy your first functions
// https://firebase.google.com/docs/functions/get-started

app.get("/api/ray", (req, res) => {
  const tracers = JSON.parse(req.query.tracers);
  if (
    !_.isArray(tracers) ||
        !_.every(tracers, (depth) => typeof depth === "number")
  ) {
    // invalid format
    res.status(422);
    res.end();
  }
  const canvas = new Canvas(243 * tracers.length, 243);
  const ctx = canvas.getContext("2d");
  for (let i = 0; i < tracers.length; i++) {
    ray(Math.round(27 / tracers[i]), 81, ctx, {x: 243, y: 0});
  }
  res.set("Cache-Control", "public, max-age=60, s-maxage=31536000");
  res.writeHead(200, {"Content-Type": "image/png"});
  canvas.pngStream().pipe(res);
});

exports.app = functions.https.onRequest(app);
