export const dateUtils = {
  ageFromDob(dob) {
    if (!dob) return "";

    const birthDate = new Date(`${dob}T00:00:00`);
    if (Number.isNaN(birthDate.getTime())) return "";

    const today = new Date();

    let age = today.getFullYear() - birthDate.getFullYear();

    const beforeBirthday =
      today.getMonth() < birthDate.getMonth() ||
      (today.getMonth() === birthDate.getMonth() &&
        today.getDate() < birthDate.getDate());

    if (beforeBirthday) age -= 1;

    return age >= 0 ? age : "";
  },

  dateFormatter(date) {
    const refractorDate = new Date(date.replace(" ", "T"));

    console.log(refractorDate);

    const formattedDate = new Intl.DateTimeFormat("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    }).format(refractorDate);

    return formattedDate;
  },
};
