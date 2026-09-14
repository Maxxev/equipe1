<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Calculateur de prix</title>

    {{--
        Styles volontairement embarques dans la page plutot que passes par Vite.
        La page reste correcte meme si "npm run build" n'a pas encore tourne sur
        le serveur, ce qui evite une demo cassee pour une raison cosmetique.
    --}}
    <style>
        :root {
            --fond: #f7f7f5;
            --carte: #ffffff;
            --texte: #1b1b18;
            --discret: #6b6b66;
            --bordure: #e3e3e0;
            --accent: #1f6feb;
            --erreur: #b42318;
            --succes: #067647;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 2rem 1rem;
            background: var(--fond);
            color: var(--texte);
            font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .page { max-width: 62rem; margin: 0 auto; }

        header { margin-bottom: 2rem; }
        header h1 { margin: 0 0 .25rem; font-size: 1.6rem; }
        header p { margin: 0; color: var(--discret); }

        .grille {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
        }

        .carte {
            background: var(--carte);
            border: 1px solid var(--bordure);
            border-radius: .6rem;
            padding: 1.25rem;
        }

        .carte h2 { margin: 0 0 .25rem; font-size: 1.05rem; }
        .carte .aide { margin: 0 0 1rem; color: var(--discret); font-size: .85rem; }

        label { display: block; margin-bottom: .75rem; font-size: .85rem; color: var(--discret); }
        label span { display: block; margin-bottom: .25rem; }

        input {
            width: 100%;
            padding: .5rem .6rem;
            border: 1px solid var(--bordure);
            border-radius: .35rem;
            font: inherit;
            background: #fff;
            color: inherit;
        }

        input:focus { outline: 2px solid var(--accent); outline-offset: 1px; }

        button {
            width: 100%;
            padding: .55rem .8rem;
            border: 0;
            border-radius: .35rem;
            background: var(--accent);
            color: #fff;
            font: inherit;
            font-weight: 500;
            cursor: pointer;
        }

        button:disabled { opacity: .6; cursor: progress; }

        .resultat {
            margin-top: 1rem;
            min-height: 1.5rem;
            font-size: .9rem;
        }

        .resultat.ok { color: var(--succes); font-weight: 600; }
        .resultat.erreur { color: var(--erreur); }

        footer {
            margin-top: 2rem;
            color: var(--discret);
            font-size: .8rem;
        }

        footer a { color: var(--accent); }

        @media (prefers-color-scheme: dark) {
            :root {
                --fond: #0f0f0e;
                --carte: #171716;
                --texte: #ededec;
                --discret: #a1a09a;
                --bordure: #2f2f2c;
                --accent: #4f9bff;
                --erreur: #ff8b7e;
                --succes: #4ade80;
            }
            input { background: #111110; }
        }
    </style>
</head>
<body>
<div class="page">
    <header>
        <h1>Calculateur de prix</h1>
        <p>Trois operations servies par l'API Laravel de l'application.</p>
    </header>

    <div class="grille">
        <section class="carte">
            <h2>Prix toutes taxes comprises</h2>
            <p class="aide">Le taux est decimal : 0.15 pour 15 %.</p>
            <form data-url="/api/calculateur/prix-ttc" data-champ="prix_ttc" data-unite="$">
                <label>
                    <span>Prix hors taxes</span>
                    <input type="number" step="any" name="prix_ht" value="100" required>
                </label>
                <label>
                    <span>Taux de taxe</span>
                    <input type="number" step="any" name="taux_taxe" value="0.15" required>
                </label>
                <button type="submit">Calculer</button>
                <p class="resultat" role="status"></p>
            </form>
        </section>

        <section class="carte">
            <h2>Prix apres remise</h2>
            <p class="aide">La remise est en pourcentage : 20 pour 20 %.</p>
            <form data-url="/api/calculateur/appliquer-remise" data-champ="prix_remise" data-unite="$">
                <label>
                    <span>Prix</span>
                    <input type="number" step="any" name="prix" value="100" required>
                </label>
                <label>
                    <span>Remise (%)</span>
                    <input type="number" step="any" name="remise_pourcentage" value="20" required>
                </label>
                <button type="submit">Appliquer</button>
                <p class="resultat" role="status"></p>
            </form>
        </section>

        <section class="carte">
            <h2>Seuil minimum</h2>
            <p class="aide">Verifie qu'un prix atteint le seuil demande.</p>
            <form data-url="/api/calculateur/respecte-seuil-minimum" data-champ="respecte_seuil_minimum">
                <label>
                    <span>Prix</span>
                    <input type="number" step="any" name="prix" value="100" required>
                </label>
                <label>
                    <span>Seuil minimum</span>
                    <input type="number" step="any" name="seuil_minimum" value="50" required>
                </label>
                <button type="submit">Verifier</button>
                <p class="resultat" role="status"></p>
            </form>
        </section>
    </div>

    <footer>
        Etat du service : <a href="{{ route('health') }}">/health</a>
    </footer>
</div>

<script>
    const jeton = document.querySelector('meta[name="csrf-token"]').content;

    // Un seul gestionnaire pour les trois formulaires : l'URL, le champ de la
    // reponse et l'unite sont lus dans les attributs data-* du formulaire.
    function afficher(zone, message, classe) {
        zone.textContent = message;
        zone.className = 'resultat ' + classe;
    }

    // Laravel renvoie soit {"message": "..."} (regle metier, 422), soit
    // {"errors": {"champ": ["..."]}} (validation). On aplatit les deux.
    function messageErreur(donnees, code) {
        if (donnees && donnees.errors) {
            return Object.values(donnees.errors).flat().join(' ');
        }
        if (donnees && donnees.message) {
            return donnees.message;
        }
        return 'Le serveur a repondu ' + code + '.';
    }

    function formater(valeur, unite) {
        if (typeof valeur === 'boolean') {
            return valeur ? 'Seuil respecte' : 'Seuil non respecte';
        }
        return unite ? valeur + ' ' + unite : String(valeur);
    }

    document.querySelectorAll('form[data-url]').forEach(function (formulaire) {
        formulaire.addEventListener('submit', async function (evenement) {
            evenement.preventDefault();

            const bouton = formulaire.querySelector('button');
            const zone = formulaire.querySelector('.resultat');
            const corps = Object.fromEntries(new FormData(formulaire).entries());

            bouton.disabled = true;
            afficher(zone, 'Calcul en cours...', '');

            try {
                const reponse = await fetch(formulaire.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': jeton,
                    },
                    body: JSON.stringify(corps),
                });

                const donnees = await reponse.json().catch(function () { return null; });

                if (!reponse.ok) {
                    afficher(zone, messageErreur(donnees, reponse.status), 'erreur');
                    return;
                }

                afficher(zone, formater(donnees[formulaire.dataset.champ], formulaire.dataset.unite), 'ok');
            } catch (erreur) {
                afficher(zone, 'Impossible de joindre le serveur.', 'erreur');
            } finally {
                bouton.disabled = false;
            }
        });
    });
</script>
</body>
</html>
