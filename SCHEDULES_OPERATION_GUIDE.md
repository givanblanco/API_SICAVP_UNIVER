# 📚 Guía de Consumo - Endpoint /Schedules/Operation

## 📋 Descripción General

El endpoint `/Schedules/Operation` retorna horarios de operación académica filtrados por año y período académico. Este endpoint realiza consultas a la base de datos **Campus** (BD PwC) con información de académicos, secciones, horarios y ubicaciones.

**URL Base:** `http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER`

**Ruta Completa:** `/Schedules/Operation`

**Método HTTP:** `GET`

---

## ✅ Validaciones Implementadas

### Parámetros Requeridos:

#### 1. **academic_year** (Año Académico)
- **Tipo:** String
- **Formato:** Números de 4 dígitos separados por comas
- **Validación:** Solo números, sin caracteres especiales
- **Ejemplos válidos:**
  - `2025` (un año)
  - `2025,2026` (múltiples años)
  - `2023,2024,2025` (varios años)
  - `2025, 2026` (espacios se limpian automáticamente)
- **Características:**
  - ✅ Eliminación automática de duplicados
  - ✅ Limpieza de espacios
  - ✅ Conversión a mayúsculas automática

#### 2. **academic_term** (Período Académico)
- **Tipo:** String
- **Formato:** Alfanuméricos de 1 a 10 caracteres separados por comas
- **Validación:** Solo letras y números
- **Ejemplos válidos:**
  - `1C` (un período)
  - `1CMA,1CMB` (múltiples períodos)
  - `1C,1CMA,1CMB` (varios períodos)
  - `1c, 1cma` (se convierten a mayúsculas: 1C, 1CMA)
- **Características:**
  - ✅ Conversión a mayúsculas automática
  - ✅ Eliminación automática de duplicados
  - ✅ Limpieza de espacios

---

## 🚨 Mensajes de Error

| Escenario | Código HTTP | Mensaje de Error |
|-----------|------------|------------------|
| Parámetro faltante | 400 | `Los parámetros academic_year y academic_term son requeridos` |
| Parámetro vacío | 400 | `Los parámetros academic_year y academic_term no pueden estar vacíos` |
| academic_year inválido | 400 | `academic_year contiene valores inválidos. Debe contener solo números separados por comas` |
| academic_term inválido | 400 | `academic_term contiene valores inválidos. Ejemplo válido: 1C,1CMA,1CMB` |
| Error en BD | 500 | `Error al conectar con la base de datos` |

---

## 🔄 Flujo de la Ruta

```
Cliente HTTP (GET)
    ↓
http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation
    ↓
CorsMiddleware::handle()
    ├─ Establece headers CORS
    ├─ Valida método HTTP
    └─ Configura Content-Type: application/json
    ↓
SchedulesOperationController::getSchedulesOperation()
    ├─ Valida presencia de parámetros GET
    ├─ Valida que no estén vacíos
    ├─ Sanitiza academic_year (validateAndSanitizeYears)
    └─ Sanitiza academic_term (validateAndSanitizeTerms)
    ↓
SchedulesOperation::getSchedulesOperation($years, $terms)
    ├─ Conecta a BD PwC (Campus)
    ├─ Construye query SQL con filtros dinámicos
    ├─ Ejecuta consulta
    ├─ Procesa resultados en array
    └─ Cierra conexión
    ↓
JsonResponse::success($data, $message)
    ├─ HTTP Status: 200 OK
    └─ Retorna JSON formateado
    ↓
Cliente HTTP (Recibe respuesta JSON)
```

---

## 📡 Ejemplos de Consumo

### 1️⃣ cURL - Básico (Un año, un período)

```bash
curl -X GET "http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation?academic_year=2025&academic_term=1C"
```

### 2️⃣ cURL - Múltiples años y períodos

```bash
curl -X GET "http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation?academic_year=2025,2026&academic_term=1C,1CMA,1CMB"
```

### 3️⃣ JavaScript / Fetch API - Básico

```javascript
const academicYear = '2025';
const academicTerm = '1C';

fetch(`http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation?academic_year=${academicYear}&academic_term=${academicTerm}`)
  .then(response => {
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    return response.json();
  })
  .then(data => {
    console.log('Éxito:', data);
    console.log('Registros:', data.data);
  })
  .catch(error => console.error('Error:', error));
```

### 4️⃣ JavaScript / Fetch API - Con URLSearchParams

```javascript
const params = new URLSearchParams({
  academic_year: '2025,2026',
  academic_term: '1C,1CMA,1CMB'
});

fetch(`http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation?${params}`)
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      console.log(`Se obtuvieron ${data.data.length} registros`);
      data.data.forEach(schedule => {
        console.log(`${schedule.NOMBRE} - ${schedule.PUBLICATION_NAME_1}`);
      });
    } else {
      console.error('Error:', data.message);
    }
  })
  .catch(error => console.error('Error de red:', error));
```

### 5️⃣ Axios - JavaScript

```javascript
const axios = require('axios');

const config = {
  method: 'get',
  url: 'http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation',
  params: {
    academic_year: '2025,2026',
    academic_term: '1C,1CMA,1CMB'
  }
};

axios(config)
  .then(response => {
    console.log('Datos:', response.data.data);
  })
  .catch(error => {
    console.error('Error:', error.response?.data?.message || error.message);
  });
```

### 6️⃣ Python - Requests

```python
import requests
import json

url = 'http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation'
params = {
    'academic_year': '2025,2026',
    'academic_term': '1C,1CMA,1CMB'
}

try:
    response = requests.get(url, params=params, timeout=10)
    response.raise_for_status()
    
    data = response.json()
    
    if data['status'] == 'success':
        print(f"✅ Se obtuvieron {len(data['data'])} registros\n")
        for schedule in data['data']:
            print(f"Académico: {schedule['NOMBRE']}")
            print(f"Materia: {schedule['PUBLICATION_NAME_1']}")
            print(f"Período: {schedule['ACADEMIC_TERM']} - Año: {schedule['ACADEMIC_YEAR']}")
            print(f"Horario: {schedule['START_TIME']} - {schedule['END_TIME']}")
            print("---")
    else:
        print(f"❌ Error: {data['message']}")
        
except requests.exceptions.RequestException as e:
    print(f"Error de conexión: {e}")
```

### 7️⃣ PHP - file_get_contents

```php
<?php
$academicYear = '2025,2026';
$academicTerm = '1C,1CMA';

$url = 'http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation';
$queryString = http_build_query([
    'academic_year' => $academicYear,
    'academic_term' => $academicTerm
]);

$fullUrl = $url . '?' . $queryString;

try {
    $response = file_get_contents($fullUrl);
    $data = json_decode($response, true);
    
    if ($data['status'] === 'success') {
        echo "✅ Se obtuvieron " . count($data['data']) . " registros\n\n";
        foreach ($data['data'] as $schedule) {
            echo "Académico: " . $schedule['NOMBRE'] . "\n";
            echo "Materia: " . $schedule['PUBLICATION_NAME_1'] . "\n";
            echo "Período: " . $schedule['ACADEMIC_TERM'] . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ Error: " . $data['message'];
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

### 8️⃣ jQuery/AJAX

```javascript
$.ajax({
  url: 'http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation',
  type: 'GET',
  data: {
    academic_year: '2025,2026',
    academic_term: '1C,1CMA,1CMB'
  },
  dataType: 'json',
  timeout: 10000,
  success: function(data) {
    if (data.status === 'success') {
      console.log('✅ Registros obtenidos:', data.data);
      
      // Ejemplo: crear tabla con los datos
      const table = $('<table>').addClass('schedules-table');
      $('<thead>').appendTo(table).append(
        $('<tr>').append(
          $('<th>').text('Nombre'),
          $('<th>').text('Materia'),
          $('<th>').text('Período'),
          $('<th>').text('Horario')
        )
      );
      
      const tbody = $('<tbody>').appendTo(table);
      $.each(data.data, function(i, schedule) {
        $('<tr>').appendTo(tbody).append(
          $('<td>').text(schedule.NOMBRE),
          $('<td>').text(schedule.PUBLICATION_NAME_1),
          $('<td>').text(schedule.ACADEMIC_TERM),
          $('<td>').text(schedule.START_TIME + ' - ' + schedule.END_TIME)
        );
      });
      
      $('#schedules-container').html(table);
    } else {
      console.error('❌ Error:', data.message);
    }
  },
  error: function(xhr, status, error) {
    console.error('Error:', xhr.status, error);
    const errorMsg = xhr.responseJSON?.message || 'Error desconocido';
    console.error('Mensaje:', errorMsg);
  }
});
```

### 9️⃣ Postman

**Pasos:**
1. Abrir Postman
2. Crear nueva solicitud → GET
3. **URL:** `http://localhost/DevNG/SICAVP/API_SICAVP_UNIVER/Schedules/Operation`
4. **Params (Query):**
   - Key: `academic_year` | Value: `2025,2026`
   - Key: `academic_term` | Value: `1C,1CMA,1CMB`
5. **Headers:** (Se establecen automáticamente)
   - `Content-Type: application/json`
6. Hacer clic en **Send**

---

## 🧪 Casos de Prueba

### ✅ Casos Válidos

**Prueba 1: Un año, un período**
```
GET /Schedules/Operation?academic_year=2025&academic_term=1C
Status: 200 OK
```

**Prueba 2: Múltiples años, múltiples períodos**
```
GET /Schedules/Operation?academic_year=2025,2026&academic_term=1C,1CMA,1CMB
Status: 200 OK
```

**Prueba 3: Con espacios (se limpian automáticamente)**
```
GET /Schedules/Operation?academic_year=2025, 2026&academic_term=1C, 1CMA, 1CMB
Status: 200 OK
(Los espacios se eliminan automáticamente)
```

**Prueba 4: Períodos en minúsculas (se convierten a mayúsculas)**
```
GET /Schedules/Operation?academic_year=2025&academic_term=1c
Status: 200 OK
(Se convierte internamente a: 1C)
```

**Prueba 5: Con duplicados (se eliminan)**
```
GET /Schedules/Operation?academic_year=2025,2025,2026&academic_term=1C,1C,1CMA
Status: 200 OK
(Se procesa como: 2025,2026 y 1C,1CMA)
```

### ❌ Casos Inválidos

**Prueba 1: Parámetro faltante**
```
GET /Schedules/Operation?academic_year=2025
Status: 400 Bad Request
Mensaje: "Los parámetros academic_year y academic_term son requeridos"
```

**Prueba 2: Ambos parámetros faltantes**
```
GET /Schedules/Operation
Status: 400 Bad Request
Mensaje: "Los parámetros academic_year y academic_term son requeridos"
```

**Prueba 3: Año con letras**
```
GET /Schedules/Operation?academic_year=202a&academic_term=1C
Status: 400 Bad Request
Mensaje: "academic_year contiene valores inválidos. Debe contener solo números separados por comas"
```

**Prueba 4: Año con menos de 4 dígitos**
```
GET /Schedules/Operation?academic_year=25&academic_term=1C
Status: 400 Bad Request
Mensaje: "academic_year contiene valores inválidos. Debe contener solo números separados por comas"
```

**Prueba 5: Período con caracteres especiales**
```
GET /Schedules/Operation?academic_year=2025&academic_term=1C@
Status: 400 Bad Request
Mensaje: "academic_term contiene valores inválidos. Ejemplo válido: 1C,1CMA,1CMB"
```

**Prueba 6: Período vacío**
```
GET /Schedules/Operation?academic_year=2025&academic_term=
Status: 400 Bad Request
Mensaje: "Los parámetros academic_year y academic_term no pueden estar vacíos"
```

**Prueba 7: Período muy largo (> 10 caracteres)**
```
GET /Schedules/Operation?academic_year=2025&academic_term=1CMABCDEFGH
Status: 400 Bad Request
Mensaje: "academic_term contiene valores inválidos. Ejemplo válido: 1C,1CMA,1CMB"
```

---

## 📊 Respuesta Exitosa (200 OK)

```json
{
  "status": "success",
  "message": "Horarios de operación SICAVP obtenidos correctamente",
  "data": [
    {
      "NUM": 1,
      "PEOPLE_CODE_ID": "P123456",
      "PREV_GOV_ID": null,
      "GOVERNMENT_ID": "12345678",
      "LAST_NAME": "PÉREZ",
      "Last_Name_Prefix": "DE",
      "FIRST_NAME": "JUAN",
      "MIDDLE_NAME": "CARLOS",
      "NOMBRE": "PÉREZ DE JUAN CARLOS",
      "ACADEMIC_YEAR": "2025",
      "ACADEMIC_TERM": "1C",
      "ACADEMIC_SESSION": "A",
      "START_DATE": "2025-01-15",
      "END_DATE": "2025-05-30",
      "EVENT_ID": "MAT001",
      "PUBLICATION_NAME_1": "MATEMÁTICAS I",
      "SECTION": "01",
      "SERIAL_ID": "001",
      "PROGRAM": "ADM",
      "PROGRAM_DESC": "ADMINISTRACIÓN",
      "CURRICULUM": "GENERAL",
      "CURRICULUMS_GEN": "",
      "FORMAL_TITLE": "CÁLCULO",
      "CLASS_LEVEL": "100",
      "CIP_CODE": "270101",
      "EVENT_STATUS": "A",
      "GENERAL_ED": "N",
      "DESC_GENERAL_ED": null,
      "ADDS": "3",
      "BUILDING_CODE": "A",
      "BUILD_NAME_1": "EDIFICIO A",
      "ROOM_ID": "101",
      "ROOM_NAME": "AULA 101",
      "DAY": "LUN",
      "CODE_DAY": "1",
      "START_TIME": "08:00:00",
      "END_TIME": "09:30:00",
      "SCHEDULED_MEETINGS": "15",
      "PLANTILLA": "SI",
      "CONTACT_HR_SESSION": "1.5",
      "MinutesClass": 90,
      "HourClass": 1.5,
      "ROUND_HourClass": 1.5,
      "VAL_HORAS": "HN",
      "FLAG_CLINIC": 0
    }
  ]
}
```

---

## 🔍 Descripción de Campos en la Respuesta

| Campo | Descripción |
|-------|-------------|
| `NUM` | Número secuencial de registro |
| `PEOPLE_CODE_ID` | ID del código de persona |
| `GOVERNMENT_ID` | Cédula o documento de identidad |
| `NOMBRE` | Nombre completo del académico |
| `ACADEMIC_YEAR` | Año académico (ej: 2025) |
| `ACADEMIC_TERM` | Período académico (ej: 1C, 1CMA) |
| `PUBLICATION_NAME_1` | Nombre de la materia/asignatura |
| `SECTION` | Sección de la clase |
| `PROGRAM_DESC` | Descripción del programa académico |
| `CURRICULUM` | Tipo de curriculum |
| `BUILD_NAME_1` | Nombre del edificio |
| `ROOM_NAME` | Nombre del aula/salón |
| `DAY` | Día de la semana (LUN, MAR, MIE, etc.) |
| `START_TIME` | Hora de inicio (HH:MM:SS) |
| `END_TIME` | Hora de finalización (HH:MM:SS) |
| `CONTACT_HR_SESSION` | Horas de contacto por sesión |
| `PLANTILLA` | Indica si tiene plantilla (SI/NO) |
| `FLAG_CLINIC` | Bandera de clínica (0/1) |

---

## ⚙️ Notas Importantes

- ✅ Los parámetros `academic_year` y `academic_term` son **OBLIGATORIOS**
- ✅ Los parámetros son **case-insensitive** para `academic_term`
- ✅ Se eliminan **duplicados automáticamente**
- ✅ Los **espacios se limpian** de los valores
- ✅ La respuesta siempre es en formato **JSON**
- ✅ **Validación estricta** de tipos de datos
- ✅ Conexión segura a **SQL Server** (BD: Campus)
- ✅ **Timeout:** 300 segundos (5 minutos)
- ✅ **Reintentos de conexión:** Hasta 5 intentos
- ✅ **Charset:** UTF-8 (soporta caracteres especiales)

---

## 🚀 Recomendaciones

1. **Manejo de Errores:** Siempre valida la respuesta antes de procesar datos
2. **Timeout:** Configura un timeout adecuado en tus llamadas (mínimo 30 segundos)
3. **Paginación:** Para consultas con muchos registros, considera implementar paginación
4. **Caché:** Implementa caché local para evitar llamadas repetidas
5. **Logs:** Registra errores y respuestas para debugging
6. **Rate Limiting:** Considera implementar límites de tasa para proteger el servidor

---

## 📞 Soporte

Para reportar problemas o sugerencias, contactar al equipo de desarrollo.

---

**Última actualización:** Enero 27, 2026  
**Versión del Endpoint:** 1.0  
**Estado:** ✅ Producción
