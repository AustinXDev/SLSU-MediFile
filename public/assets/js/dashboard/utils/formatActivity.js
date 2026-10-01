export function formatActivity(activity) {
  if (activity.description) {
    return activity.description;
  }

  return activity.action || "System activity";
}
