import { getCatalogContext } from "./catalog-context.js";
import { GEMINI_API_KEY as EMBEDDED_GEMINI_KEY } from "./secrets.js";

/**
 * Modelos en orden: si tu clave no tiene cuota en uno (p. ej. gemini-2.0-flash),
 * se prueba el siguiente. Ajusta el orden en AI Studio según lo que tengas habilitado.
 */
const GEMINI_MODELS = [
  "gemini-2.0-flash-lite",
  "gemini-1.5-flash-8b",
  "gemini-flash-latest",
  "gemini-2.5-flash",
  "gemini-2.0-flash",
];

function buildSystemInstruction() {
  return `
Eres el "Asistente de Compras Merkalisto" del supermercado Inversiones Merkalisto en Villa de San Diego de Ubaté, Cundinamarca, Colombia.

REGLAS ESTRICTAS:
- Solo respondes sobre: productos del catálogo, precios aproximados en COP, categorías, sugerencias de compra en supermercado, pedidos/recogida en Ubaté de forma general (sin inventar políticas legales ni datos sensibles).
- Si preguntan algo fuera de comercio minorista, salud clínica, política, programación u otros temas: declina con cortesía y ofrece ayuda sobre compras en Merkalisto.
- Usa español colombiano claro y breve.
- Si no tienes un precio exacto en el contexto, di que es orientativo y que pueden ver el plan en tienda o el catálogo publicado.
- Menciona la ubicación (Villa de San Diego de Ubaté) cuando sea relevante para entrega o recogida.

CONTEXTO DE CATÁLOGO (referencia):
${getCatalogContext()}
`.trim();
}

export function initChatbot() {
  const toggle = document.getElementById("chat-toggle");
  const panel = document.getElementById("chat-panel");
  const closeBtn = document.getElementById("chat-close");
  const form = document.getElementById("chat-form");
  const input = document.getElementById("chat-input");
  const messages = document.getElementById("chat-messages");
  const keyInput = document.getElementById("gemini-key-input");
  const keyHint = document.getElementById("chat-key-hint");

  if (!toggle || !panel || !form || !input || !messages) return;

  const STORAGE_KEY = "merkalisto_gemini_api_key";

  const savedKey = sessionStorage.getItem(STORAGE_KEY) || localStorage.getItem(STORAGE_KEY);
  if (keyInput) {
    if (savedKey) keyInput.value = savedKey;
    else if (EMBEDDED_GEMINI_KEY) keyInput.value = EMBEDDED_GEMINI_KEY;
  }

  function openPanel() {
    panel.hidden = false;
    panel.setAttribute("aria-hidden", "false");
    toggle.setAttribute("aria-expanded", "true");
    input.focus();
  }

  function closePanel() {
    panel.hidden = true;
    panel.setAttribute("aria-hidden", "true");
    toggle.setAttribute("aria-expanded", "false");
  }

  toggle.addEventListener("click", () => {
    if (panel.hidden) openPanel();
    else closePanel();
  });
  closeBtn?.addEventListener("click", closePanel);

  function appendMessage(role, text) {
    const row = document.createElement("div");
    row.className = `chat-msg chat-msg--${role}`;
    row.textContent = text;
    messages.appendChild(row);
    messages.scrollTop = messages.scrollHeight;
  }

  function shouldTryNextModel(res, data) {
    const status = data?.error?.status || "";
    const msg = String(data?.error?.message || "").toLowerCase();
    if (res.status === 404) return true;
    if (res.status === 429) return true;
    if (status === "RESOURCE_EXHAUSTED") return true;
    if (status === "NOT_FOUND") return true;
    if (msg.includes("quota")) return true;
    if (msg.includes("not found") || msg.includes("is not found")) return true;
    return false;
  }

  function isFatalClientError(res, data) {
    const status = data?.error?.status || "";
    if (res.status === 401 || res.status === 403) return true;
    if (status === "PERMISSION_DENIED" || status === "UNAUTHENTICATED") return true;
    if (status === "INVALID_ARGUMENT" && String(data?.error?.message || "").toLowerCase().includes("api key"))
      return true;
    return false;
  }

  async function sendToGemini(userText, apiKey) {
    const body = {
      systemInstruction: {
        parts: [{ text: buildSystemInstruction() }],
      },
      contents: [{ parts: [{ text: userText }] }],
      generationConfig: {
        temperature: 0.4,
        maxOutputTokens: 512,
      },
    };

    let lastErr = "Error en la API";

    for (const model of GEMINI_MODELS) {
      const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${encodeURIComponent(apiKey)}`;
      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });

      const data = await res.json().catch(() => ({}));

      if (res.ok) {
        const text =
          data?.candidates?.[0]?.content?.parts?.map((p) => p.text).join("") ||
          "No pude generar una respuesta. Intenta de nuevo.";
        return text.trim();
      }

      lastErr = data?.error?.message || res.statusText || "Error en la API";

      if (isFatalClientError(res, data)) {
        throw new Error(lastErr);
      }
      if (shouldTryNextModel(res, data)) {
        continue;
      }
      throw new Error(lastErr);
    }

    throw new Error(
      `${lastErr}\n\n` +
        "Ningún modelo respondió con tu cuota actual. En https://aistudio.google.com revisa límites, " +
        "activa facturación si aplica o crea una API key en un proyecto con Generative Language API habilitada."
    );
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const apiKey =
      (keyInput?.value || "").trim() || (typeof EMBEDDED_GEMINI_KEY === "string" ? EMBEDDED_GEMINI_KEY.trim() : "");
    const userText = input.value.trim();
    if (!userText) return;

    if (!apiKey) {
      if (keyHint) keyHint.hidden = false;
      keyInput?.focus();
      return;
    }
    if (keyHint) keyHint.hidden = true;
    sessionStorage.setItem(STORAGE_KEY, apiKey);

    appendMessage("user", userText);
    input.value = "";

    const typing = document.createElement("div");
    typing.className = "chat-msg chat-msg--assistant chat-msg--typing";
    typing.textContent = "Escribiendo…";
    messages.appendChild(typing);
    messages.scrollTop = messages.scrollHeight;

    try {
      const reply = await sendToGemini(userText, apiKey);
      typing.remove();
      appendMessage("assistant", reply);
    } catch (err) {
      typing.remove();
      appendMessage("assistant", `Error: ${err.message || String(err)}`);
    }
  });
}
