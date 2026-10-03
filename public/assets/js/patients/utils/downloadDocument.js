export function downloadDocument() {
  const element = document.querySelector(".document");

  if (!element) {
    console.error("Document page wrapper not found.");
    return;
  }

  const options = {
    margin: 0,

    filename: "patient-record.pdf",

    image: {
      type: "jpeg",
      quality: 1,
    },

    html2canvas: {
      scale: 2,
      useCORS: true,
      backgroundColor: "#ffffff",
    },

    jsPDF: {
      unit: "mm",
      format: "a4",
      orientation: "portrait",
    },

    pagebreak: {
      mode: ["css", "legacy"],
      before: ".document-page + .document-page",
    },
  };

  element.classList.add("pdf-export");

  try {
    const exportPromise = html2pdf().set(options).from(element).save();
    return Promise.resolve(exportPromise).finally(() => {
      element.classList.remove("pdf-export");
    });
  } catch (error) {
    element.classList.remove("pdf-export");
    throw error;
  }
}
