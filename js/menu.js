/**
 * Navegación lateral y secciones principales.
 */
export function initMenu() {
  const nav = document.querySelector(".sidebar-nav");
  const sections = document.querySelectorAll("[data-section]");
  if (!nav || !sections.length) return;

  nav.querySelectorAll("a[data-target]").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const id = link.getAttribute("data-target");
      const target = document.getElementById(id);
      if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "start" });
        nav.querySelectorAll("a").forEach((a) => a.classList.remove("active"));
        link.classList.add("active");
      }
    });
  });

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const id = entry.target.id;
        const activeLink = nav.querySelector(`a[data-target="${id}"]`);
        if (activeLink) {
          nav.querySelectorAll("a").forEach((a) => a.classList.remove("active"));
          activeLink.classList.add("active");
        }
      });
    },
    { rootMargin: "-40% 0px -45% 0px", threshold: 0 }
  );

  sections.forEach((sec) => observer.observe(sec));
}
