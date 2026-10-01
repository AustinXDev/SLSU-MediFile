export function calculateIdealWeight(heightCm, gender) {
  const height = Number(heightCm);

  if (!height || !gender) {
    return null;
  }

  const heightInches = height / 2.54;
  const normalizedGender = gender.toLowerCase();

  let baseWeight;

  if (normalizedGender === "male" || normalizedGender === "m") {
    baseWeight = 50;
  } else if (normalizedGender === "female" || normalizedGender === "f") {
    baseWeight = 45.5;
  } else {
    return null;
  }

  return Number(baseWeight + 2.3 * (heightInches - 60)).toFixed(1);
}
