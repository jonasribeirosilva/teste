<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despesas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="container" style="margin-top: 10px;">
        <div class="card">
            <div class="card-body">
                @if ($isNew)
                <h1>Criar Despesas</h1>
                @else
                <h1>Editar Despesas</h1>
                @endif
                <form id="frm_despesas">
                    @csrf
                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <input value="{{ $despesa->descricao }}" type="text" id="descricao" name="descricao" maxlength="191" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-xs-12">
                            <div class="form-group">
                                <label for="data">Data:</label>
                                <input value="{{ $despesa->data }}" type="date" id="data" name="data" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 col-xs-12">
                            <div class="form-group">
                                <label for="valor">Valor:</label>
                                <input value="{{ $despesa->valor }}" type="number" step="0.01" id="valor" name="valor" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 col-xs-12">
                            <div class="form-group">
                                <label for="user">Usuário:</label>
                                <select name="user" id="user" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($users as $user)
                                        @if ($despesa->usuario == $user->id)
                                        <option selected value="{{ $user->id }}">{{ $user->name }}</option>
                                        @else
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <a href="/despesas" class="btn btn-link">Voltar</a>
                    <button class="btn btn-primary" type="submit">Salvar</button>
                </form>
            </div>
        </div>
    </div>

<script>
    const frmDespesas = document.getElementById('frm_despesas');
    const handleResponse = response => {
        return response.text().then(text => {
            const data = text && JSON.parse(text);
            if (!response.ok) {
                const error = (data && data.error) || response.statusText;
                const code = (data && data.code) || response.status;
                const status = response.status;
                return Promise.reject({code, error, status});
            }

            return data;
        });
    }
    frmDespesas.addEventListener('submit', (ev) => {
        ev.preventDefault();

        @if ($isNew)
            let endpoint = '/despesas/create'
            let method = 'POST'
        @else
            let endpoint = '/despesas/{{ $despesa->id }}'
            let method = 'PUT'
        @endif

        let formData = new FormData(frmDespesas);

        fetch(endpoint, {
            method: method,
            body: formData,
            credentials: "include"
        }).then(handleResponse).then(res => {
            // window.location = '/despesas'
            return res
        }).catch(({error}) => {
            alert("Error: " + error)
        })
    })
</script>
</body>
</html>