document.addEventListener("visibilitychange", async function () {
  if (!document.hidden) {
    try {
      const res = await fetch("/version.json?nocache=" + Date.now());
      const data = await res.json();

      if (data.version !== window.siteVersion) {
        location.reload();
      }
    } catch (e) {
      console.log("Error verificando versión", e);
    }
  }
});
