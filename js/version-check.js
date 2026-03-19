let currentVersion = null;

async function checkVersion() {
  const res = await fetch("/version.json?nocache=" + Date.now());
  const data = await res.json();

  if (currentVersion && currentVersion !== data.version) {
    location.reload();
  }

  currentVersion = data.version;
}

checkVersion();

document.addEventListener("visibilitychange", function () {
  if (!document.hidden) {
    checkVersion();
  }
});
