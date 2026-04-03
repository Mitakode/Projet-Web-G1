#!/usr/bin/env python3
"""
search_photos.py – Recherche de photos par requête textuelle ou par nom de personne.

Modes d'utilisation :
  Recherche par texte   : python search_photos.py --query "toboggan aquatique" --size 5
  Recherche par nom     : python search_photos.py --name "Papi" --size 3
  Avec endpoint API     : python search_photos.py --name "Dupont" --size 10 --endpoint http://localhost:9200/photos/_search

Variables d'environnement (optionnelles, mêmes valeurs par défaut que config.php) :
  DB_HOST      (défaut : 90.54.20.90)
  DB_NAME      (défaut : projet_db)
  DB_USER      (défaut : projet_user)
  DB_PASS      (défaut : projet_pass)
  DB_PORT      (défaut : 3306)
  PHOTO_API_URL (défaut : valeur de --endpoint si fournie)
"""

import argparse
import json
import os
import sys

# ---------------------------------------------------------------------------
# Dépendances optionnelles
# ---------------------------------------------------------------------------
try:
    import mysql.connector
    MYSQL_AVAILABLE = True
except ImportError:
    MYSQL_AVAILABLE = False

try:
    import requests as _requests
    REQUESTS_AVAILABLE = True
except ImportError:
    REQUESTS_AVAILABLE = False


# ---------------------------------------------------------------------------
# Connexion à la base de données
# ---------------------------------------------------------------------------

def _db_connect():
    """Retourne une connexion MySQL en utilisant les variables d'environnement."""
    if not MYSQL_AVAILABLE:
        raise ImportError(
            "Le module 'mysql-connector-python' est requis pour la recherche par nom.\n"
            "Installez-le avec : pip install mysql-connector-python"
        )
    return mysql.connector.connect(
        host=os.environ.get("DB_HOST", "90.54.20.90"),
        database=os.environ.get("DB_NAME", "projet_db"),
        user=os.environ.get("DB_USER", "projet_user"),
        password=os.environ.get("DB_PASS", "projet_pass"),
        port=int(os.environ.get("DB_PORT", 3306)),
    )


# ---------------------------------------------------------------------------
# Recherche de personnes par nom
# ---------------------------------------------------------------------------

def find_person_ids(name: str) -> list[int]:
    """
    Cherche dans la table `Utilisateur` les utilisateurs dont le Nom ou le Prénom
    correspond (insensible à la casse) à `name`. Retourne la liste des Id_user trouvés.
    """
    conn = _db_connect()
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT Id_user, Nom, Prenom
            FROM Utilisateur
            WHERE LOWER(Nom) LIKE LOWER(%s)
               OR LOWER(Prenom) LIKE LOWER(%s)
            """,
            (f"%{name}%", f"%{name}%"),
        )
        rows = cursor.fetchall()
        if not rows:
            print(f"[!] Aucun utilisateur trouvé pour le nom : {name!r}", file=sys.stderr)
            return []
        for row in rows:
            print(
                f"[✓] Personne trouvée – Id={row['Id_user']}, "
                f"Nom={row['Nom']!r}, Prénom={row['Prenom']!r}",
                file=sys.stderr,
            )
        return [row["Id_user"] for row in rows]
    finally:
        cursor.close()
        conn.close()


# ---------------------------------------------------------------------------
# Construction du corps de la requête
# ---------------------------------------------------------------------------

def build_request_body(
    *,
    query: str | None = None,
    name: str | None = None,
    size: int = 10,
) -> dict:
    """
    Construit le corps de la requête de recherche de photos.

    - Si `name` est fourni  → recherche par person_id (récupérés depuis la DB)
    - Si `query` est fourni → recherche par texte libre
    - Les deux peuvent être combinés.
    """
    body: dict = {"size": size}

    if name:
        person_ids = find_person_ids(name)
        if not person_ids:
            print(
                f"[!] Impossible de construire la requête : aucun Id_user pour {name!r}.",
                file=sys.stderr,
            )
        body["person_id"] = person_ids

    if query:
        body["query"] = query

    return body


# ---------------------------------------------------------------------------
# Envoi de la requête à l'API
# ---------------------------------------------------------------------------

def send_request(endpoint: str, body: dict) -> dict | None:
    """Envoie un POST JSON à `endpoint` et retourne la réponse JSON."""
    if not REQUESTS_AVAILABLE:
        raise ImportError(
            "Le module 'requests' est requis pour envoyer la requête.\n"
            "Installez-le avec : pip install requests"
        )
    response = _requests.post(endpoint, json=body, timeout=10)
    response.raise_for_status()
    return response.json()


# ---------------------------------------------------------------------------
# Point d'entrée
# ---------------------------------------------------------------------------

def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Recherche de photos par requête ou par nom de personne.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__,
    )
    group = parser.add_mutually_exclusive_group()
    group.add_argument(
        "--query", "-q",
        metavar="TEXTE",
        help="Requête textuelle (ex: 'toboggan aquatique').",
    )
    group.add_argument(
        "--name", "-n",
        metavar="NOM",
        help="Nom ou prénom de la personne à rechercher dans la base de données.",
    )
    parser.add_argument(
        "--size", "-s",
        type=int,
        default=10,
        metavar="N",
        help="Nombre de photos à récupérer (défaut : 10).",
    )
    parser.add_argument(
        "--endpoint", "-e",
        metavar="URL",
        default=os.environ.get("PHOTO_API_URL"),
        help="URL de l'API de recherche de photos (optionnel). "
             "Si absent, le corps de la requête est simplement affiché.",
    )
    return parser.parse_args()


def main() -> None:
    args = parse_args()

    if not args.query and not args.name:
        print("[!] Veuillez fournir --query ou --name.", file=sys.stderr)
        sys.exit(1)

    if args.size <= 0:
        print("[!] --size doit être un entier strictement positif.", file=sys.stderr)
        sys.exit(1)

    # Construction du corps
    body = build_request_body(
        query=args.query,
        name=args.name,
        size=args.size,
    )

    print("\n[Corps de la requête]")
    print(json.dumps(body, ensure_ascii=False, indent=2))

    # Envoi si endpoint fourni
    if args.endpoint:
        print(f"\n[→] Envoi vers {args.endpoint} …", file=sys.stderr)
        try:
            result = send_request(args.endpoint, body)
            print("\n[Réponse de l'API]")
            print(json.dumps(result, ensure_ascii=False, indent=2))
        except Exception as exc:  # noqa: BLE001
            print(f"[✗] Erreur lors de l'envoi : {exc}", file=sys.stderr)
            sys.exit(1)
    else:
        print(
            "\n[i] Aucun endpoint fourni. "
            "Utilisez --endpoint <URL> ou la variable d'environnement PHOTO_API_URL "
            "pour envoyer la requête.",
            file=sys.stderr,
        )


if __name__ == "__main__":
    main()
