const transformURL = (url) => {
    return url.toLowerCase().replace('/', '.');
}

const getDaysClean = () => {
    let dias = [];
    let letrasDias = ["L", "M", "X", "J", "V"];
    for (const letraDia of letrasDias) {
        let dia = {
            dia: letraDia,
            cantidad: 0
        };
        dias.push(dia);
    }
    return dias;
}

const getDay = (day) => {
    switch (day) {
        case 'L': return 'Lunes';
        case 'M': return 'Martes';
        case 'X': return 'Miércoles';
        case 'J': return 'Jueves';
        case 'V': return 'Viernes';
        default: return day;
    }
}

const getTypePase = (tipo) => {
    switch (tipo) {
        case 'panel': return 'Panel principal';
        case 'auspicio': return 'Auspicio';
        case 'asociado': return 'Asociado';
        case 'stand': return 'Stand';
        case 'prensa': return 'Prensa';
        case 'actividad_social': return 'Actividad Social';
        case 'institucion_aliada': return 'Institución Aliada';
        default: return 'Error';
    }
}

const currency = (valor, simbolo) => {
    const price = parseFloat(valor);
    if(simbolo == 'USD'){
        return price.toLocaleString( 'en-US', {
            style: 'currency',
            currency: 'USD'
        });
    }else{
        return price.toLocaleString( 'es-PE', {
            style: 'currency',
            currency: 'PEN'
        });

    }

}

const loadDepartamentos = async (data) => {
    const request = await axios.post('/padre/departamentos', { id: data })
        .then(response => {
            if (response.status != 200) {
                throw new Error('Ocurrió un error al obtener los datos.');
            }
            return response;
        }).then(data => {
            return data.data.departamentos
        }).catch(error => {
            console.error('Error:', error.message);
        });
    return request;
}

const loadProvincias = async (id_pais, id_departamento) => {
    const request = await axios.post('/padre/provincias', { id_pais: id_pais, id_departamento: id_departamento })
        .then(response => {
            if (response.status != 200) {
                throw new Error('Ocurrió un error al obtener los datos.');
            }
            return response;
        }).then(data => {
            return data.data.provincias
        }).catch(error => {
            console.error('Error:', error.message);
        });
    return request;
}

const loadDistritos = async (id_pais, id_departamento, id_provincia) => {
    const request = await axios.post('/padre/distritos', { id_pais: id_pais, id_departamento: id_departamento, id_provincia: id_provincia })
        .then(response => {
            if (response.status != 200) {
                throw new Error('Ocurrió un error al obtener los datos.');
            }
            return response;
        }).then(data => {
            return data.data.distritos
        }).catch(error => {
            console.error('Error:', error.message);
        });
    return request;
}

const openFile = (file) => {
    window.open('https://ecommerce.perumin.com/storage/' + file, '_blank');
}

const getPeruApis = async (site, type, documento,id_tipo_documento) => {
    const request = await axios.post('/api/peru', { url: site, type: type, documento: documento ,id_tipo_documento :id_tipo_documento})
        .then(response => {
            if (response.status != 200) {
                throw new Error('Ocurrió un error al obtener los datos.');
            }
            return response;
        }).then(data => {
            return data.data
        }).catch(error => {
            console.error('Error:', error.message);
        });
    return request;
}

const getCartCurrency = async () => {
    const request = await axios.post('/padre/currency', { })
        .then(response => {
            if (response.status != 200) {
                throw new Error('Ocurrió un error al obtener los datos.');
            }
            return response;
        }).then(data => {
            return data.data
        }).catch(error => {
            console.error('Error:', error.message);
        });
    return request;
}

const detectDeviceType = () =>
    /Mobile|Android|iPhone|iPad/i.test(navigator.userAgent)
      ? 'Mobile'
      : 'Desktop';

const hasPermission= (listado_permisos, permiso) =>{

    var FindIndex = Object.values(listado_permisos).find((element) => element == permiso);
    return (FindIndex >= 0);

}

function toLocalDateOnly(dateStr) {
  const utcDate = new Date(dateStr);
  utcDate.setDate(utcDate.getDate() + 1);
  return utcDate;
}

const preventChangeInput= (e) =>{
    e.preventDefault();
}

export default {
    transformURL,
    getDaysClean,
    getDay,
    getTypePase,
    currency,
    loadDepartamentos,
    loadProvincias,
    loadDistritos,
    openFile,
    getPeruApis,
    detectDeviceType,
    preventChangeInput,
    hasPermission,
    getCartCurrency,
    toLocalDateOnly,
}
