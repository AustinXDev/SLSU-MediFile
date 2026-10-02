export async function post(url, data) {
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-type": "application/json",
    },
    body: JSON.stringify(data),
  });

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || "Request failed.");
  }

  return result;
}

export function get(url) {
  const response = fetch(url, {
    method: "GET",
    header: {
      "Content-type": "application/json",
    },
  });
}
