function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Appelle un endpoint de conversion et normalise les erreurs.
 * Rejette avec un message d'erreur compréhensible dans tous les cas
 * (validation serveur, erreur métier ou erreur réseau).
 */
export async function postConversion(url, payload) {
    let response;

    try {
        response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(payload),
        });
    } catch {
        throw new Error("Impossible de contacter le serveur. Vérifiez votre connexion et réessayez.");
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        if (data?.errors) {
            const premiereErreur = Object.values(data.errors)[0]?.[0];
            throw new Error(premiereErreur ?? data?.message ?? 'La valeur saisie est invalide.');
        }

        throw new Error(data?.message ?? 'La valeur saisie est invalide.');
    }

    return data;
}
