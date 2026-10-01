export function fitDocumentPages() {
  const viewer = document.querySelector(".document-scroll");

  if (!viewer) return;

  const pages = viewer.querySelectorAll(".document-page");

  if (!pages.length) return;

  const availableWidth = viewer.clientWidth - 32;

  pages.forEach((page) => {
    const wrapper = page.parentElement;

    if (!wrapper || !wrapper.classList.contains("document-page-wrapper")) {
      return;
    }

    const pageWidth = page.offsetWidth;
    const pageHeight = page.offsetHeight;

    const scale = Math.min(1, availableWidth / pageWidth);

    page.style.transform = `scale(${scale})`;

    wrapper.style.width = `${pageWidth * scale}px`;
    wrapper.style.height = `${pageHeight * scale}px`;
  });
}
