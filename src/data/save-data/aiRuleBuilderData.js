const BASE  = gwpdrPluginData.restUrl + 'ai-rule-builder';
const NONCE = gwpdrPluginData.nonce;

const post = async (endpoint, body) => {
    const res  = await fetch(BASE + endpoint, {
        method:  'POST',
        headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' },
        body:    JSON.stringify(body),
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
        return {
            success: false,
            message: json.message || `Server error (${res.status}). Check that Smart Engine has a connected AI provider.`,
        };
    }
    return json;
};

/** Generate a brand-new rule from a natural-language prompt. */
export const generateAiRule = (prompt) => post('/generate', { prompt });

/** Modify an existing rule based on a change instruction. */
export const modifyAiRule = (currentRule, changePrompt) =>
    post('/modify', { prompt: changePrompt, current_rule: currentRule });
