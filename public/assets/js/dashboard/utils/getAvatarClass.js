export function getAvatarClass(username) {
  const classes = ["pink", "blue", "purple", "orange"];

  if (!username) {
    return classes[0];
  }

  let hash = 0;

  for (let i = 0; i < username.length; i++) {
    hash = username.charCodeAt(i) + ((hash << 5) - hash);
  }

  return classes[Math.abs(hash) % classes.length];
}
