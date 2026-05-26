<x-mail::message>
# Tu es invité à jouer ! 🎮

Bonjour,

Tu as été invité à rejoindre une partie **City Play** dans l'environnement **{{ $nomEnvironnement }}**.

Clique sur le bouton ci-dessous pour rejoindre la partie. Ce lien est valable **5 heures**.

<x-mail::button :url="$lien" color="primary">
Rejoindre la partie
</x-mail::button>

Si tu n'es pas à l'origine de cette demande, ignore simplement cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>