const queryForm = document.getElementById('query-form')
const filters = document.getElementsByClassName('filter')
const sorters = document.getElementsByClassName('sorter')

let i

window.addEventListener('load', () => {
    for (i of sorters) {
        const params = Object.assign({}, queryParams)
        let cssClass = ''

        params.sort = i.attributes['data-field'].value
        sortDir = ""
        label = i.innerHTML

        if (params.sort === queryParams.sort) {
            params.sortDir = queryParams.sortDir === 'asc' ? 'desc' : 'asc'
            cssClass = queryParams.sortDir === 'asc' ? 'sort-asc' : 'sort-desc'
            sortDir = `<span class="sort-dir"></span>`
        } else {
            params.sortDir = 'asc'
        }

        const url = location.origin + location.pathname + '?' + serialize(params)

        i.innerHTML = `<a href="${url}" class="${cssClass}">${label}${sortDir}</a>`
    }
})

queryForm.addEventListener('submit', (event) => {
    const defaults = objectToQueryParams(defaultParams)

    event.target.querySelectorAll('input, select, textarea').forEach((item) => {
        if ([null, ''].includes(item.value) || (String(item.value) === String(defaults[item.name]))) {
            event.target.elements[item.name].remove()
        }
    })
})

for (i of filters) {
    i.addEventListener('change', (event) => {
        const control = event.target
        queryForm.elements[control.name].value = control.value
        queryForm.requestSubmit()
    })
}

document.getElementById('perPage-select')
    ?.addEventListener('change', (event) => {
        const input = queryForm.elements['perPage']
        input.value = event.target.value
        queryForm.requestSubmit()
    })

isObject = (obj) => {
    return typeof obj === 'object'
        && obj !== null
        && !Array.isArray(obj)
}

serialize = (obj, prefix = null, skipEmpty = true) => {
    let str = [], p

    for (p in obj) {
        if (obj.hasOwnProperty(p)) {
            let k = prefix ? prefix + "[" + p + "]" : p,
                v = obj[p]

            if (!(skipEmpty && [null, ''].includes(v))) {
                str.push(isObject(v) ?
                    serialize(v, k) :
                    encodeURIComponent(k) + "=" + encodeURIComponent(v))
            }
        }
    }

    return str.join("&")
}

function objectToQueryParams(obj, parentKey = '', result = {}) {
    for (const [key, value] of Object.entries(obj)) {
        const fullKey = parentKey
            ? `${parentKey}[${key}]`
            : key;

        if (
            value !== null &&
            typeof value === 'object' &&
            !Array.isArray(value)
        ) {
            objectToQueryParams(value, fullKey, result);
        } else {
            result[fullKey] = String(value);
        }
    }

    return result;
}
