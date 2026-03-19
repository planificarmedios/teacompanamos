const fs = require("fs");

const newVersion = Date.now().toString();

// actualizar version.json
fs.writeFileSync(
  "version.json",
  JSON.stringify({ version: newVersion }, null, 2),
);

// actualizar index.html
let html = fs.readFileSync("index.html", "utf8");

html = html.replace(
  /window\.siteVersion\s*=\s*".*?"/,
  `window.siteVersion = "${newVersion}"`,
);

fs.writeFileSync("index.html", html);

console.log("✔ Versión actualizada:", newVersion);
