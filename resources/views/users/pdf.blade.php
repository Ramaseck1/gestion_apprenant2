<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
</head>
<body>
    <h1>Liste des utilisateurs</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->nom }}</td>
                    <td>{{ $user->prenom }}</td>
                    <td>{{ $user->telephone }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
