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
                <h1>Despesas</h1>

                <form id="frm_filter">
                    <div class="form-group">
                        <label for="user">Usuário:</label>
                        <select name="user" id="user" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-primary" type="submit">Filtrar</button>
                    <a href="/despesas/create" class="btn btn-success">Cadastrar despesa</a>
                </form>
                <table class="table" id="tb_despesas">
                    <thead>
                        <tr>
                            <th width="1">#</th>
                            <th>Descrição</th>
                            <th width="1">Usuário</th>
                            <th width="1">Data</th>
                            <th width="1">Valor</th>
                            <th width="1">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tb_despesas_body">
                        <tr>
                            <td colspan="6">
                                Aguarde.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
    const tbDespesasBody = document.getElementById('tb_despesas_body');
    const frmFilter = document.getElementById('frm_filter');
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
    const tableDespesas = {
        data: [],
        params: {},
        numResults: 0,
        numResultsTotals: 0,
        page: 0,
        code: 0,
        error: '',
        prevPage() {
            if (this.page < 0) return false;

            this.search(Object.assign(this.params, {page: this.page--}))
        },
        nextPage() {
            let maxPage = (this.numResultsTotals % 50) + 1
            if (this.page > maxPage) return false;

            this.search(Object.assign(this.params, {page: this.page++}))
        },
        search(params = {}) {
            this.params = params
            if (typeof params['page'] != 'undefined') {
                this.page = params['page']
            } else {
                params['page'] = this.page 
            }
            let paramsGet = new URLSearchParams('');
            for (const key in params) {
                if (Object.hasOwnProperty.call(params, key)) {
                    if (params[key] === '' || params[key] === null) {
                        continue;
                    }
                    paramsGet.append(key, params[key])
                }
            }
            fetch('/despesas/list?' + paramsGet.toString(), {
                credentials: "include"
            }).then(handleResponse).then(({numResults, numResultsTotals, data}) => {
                tableDespesas.numResults = numResults
                tableDespesas.numResultsTotals = numResultsTotals
                tableDespesas.data = data
                tableDespesas.render()
            }).catch(({code, error}) => {
                tableDespesas.code = code
                tableDespesas.error = error
                tableDespesas.render()
            })
        },
        delete(despesa){
            if (confirm("Deseja apagar esta despesa?")) {
                fetch(`/despesas/${despesa}`, {
                    method: 'DELETE',
                    credentials: "include"
                }).then(handleResponse).then(() => {
                    tableDespesas.search(this.params)
                }).catch(({code, error}) => {
                    tableDespesas.code = code
                    tableDespesas.error = error
                    tableDespesas.render()
                })
            }
        },
        formatDate(v) {
            if (v == '' || v == null) return '-'

            let d = new Date(v)
            return d.toLocaleDateString()
        },
        formatMoney(v) {
            if (v == '' || v == null) return '-'

            return new Intl.NumberFormat('pt-BR',
                {
                    style: 'currency',
                    currency: 'BRL'
                }).format(v);
        },
        render() {
            let html = ''

            if (this.error != '') {
                html += `<tr><td colspan="6" class="text-danger">${this.error}</td></tr>`
            }
            html += `<tr>`
            for (const key in this.data) {
                if (Object.hasOwnProperty.call(this.data, key)) {
                    const row = this.data[key];   
                    html += `<td><nobr>${row.id}</nobr></td>`
                    html += `<td>${row.descricao}</td>`
                    html += `<td>`
                    html += `<nobr>${row.user_name}`
                    html += `<small style="color:#777;">#${row.user_id}</small></nobr></td>`
                    html += `<td><nobr>` + this.formatDate(row.data) + `</nobr></td>`
                    html += `<td><nobr>` + this.formatMoney(row.valor) + `</nobr></td>`
                    html += `<td><nobr>`
                    html += `<a href="/despesas/${row.id}/edit" class="btn btn-primary">`
                    html += `<i class="fas fa-pencil-alt"></i>`
                    html += `</a> `
                    html += `<button onclick="tableDespesas.delete(${row.id})" class="btn btn-danger">`
                    html += `<i class="fas fa-trash"></i>`
                    html += `</button></nobr></td>`
                }
            }
            html += `</tr>`

            tbDespesasBody.innerHTML = html
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        tableDespesas.search({page: 0})
    })

    frmFilter.addEventListener('submit', (ev) => {
        ev.preventDefault();

        let user = document.getElementById('user').value;
        tableDespesas.search({user, page: 0})
    })
</script>
</body>
</html>