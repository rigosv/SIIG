#Catálogos
Las siguientes opciones están ubicadas en el menú principal **Catálogos**

## Fuente de datos
![Fuente de datos](images/fuente_datos.png)

- **Contacto:** Nombre de la persona que proporciona datos.
- **Establecimiento:** Organismo al que pertenece el contacto.
- **Correo electrónico:** Correo electrónico oficial del contacto.
- **Número telefónico:** Número telefónico del contacto.
- **Cargo:** Cargo que ocupa el contacto dentro del organismo al que pertenece.

## Responsable de datos
![Responsable de datos](images/responsable_datos.png)
- **Contacto:** Nombre de la persona que es reponsable de los datos y la calidad de estos (Podría ser el mismo que en Fuente de datos).
- **Establecimiento:** Organismo al que pertenece el contacto.
- **Correo electrónico:** Correo electrónico oficial del contacto.
- **Número telefónico:** Número telefónico del contacto.
- **Cargo:** Cargo que ocupa el contacto dentro del organismo al que pertenece.


## Responsable indicador
![Responsable de indicador](images/responsable_indicador.png)
- **Contacto:** Nombre de la persona que es reponsable de hacer el seguimiento y control del indicador.
- **Establecimiento:** Organismo al que pertenece el contacto.
- **Correo electrónico:** Correo electrónico oficial del contacto.
- **Número telefónico:** Número telefónico del contacto.
- **Cargo:** Cargo que ocupa el contacto dentro del organismo al que pertenece.

## Clasificación de privacidad
![Clasificación privacidad](images/clasificacion_privacidad.png)

- **Código:** Clave con el que será identificado de manera técnica.
- **Descripción:** Nombre de la Clasificación de privacidad.
- **Comentario:** Nota o comentario que se quiera agregar, opcional.

## Clasificación según nivel
![Clasificación según nivel](images/clasificacion_nivel.png)

- **Código:** Clave con el que será identificado de manera técnica.
- **Descripción:** Nombre de la Clasificación según nivel. Indica el nivel máximo que abarcan los datos de un indicador: Regional, Establecimiento, Departamental, Nacional, entre otros.
- **Comentario:** Nota o comentario que se quiera agregar, opcional.


## Clasificación según uso
![Clasificación según uso](images/clasificacion_uso.png)

- **Código:** Clave con el que será identificado de manera técnica.
- **Descripción:** Nombre de la clasificación según uso. Indica el uso para el que puede estar destinado un indicador.
- **Comentario:** Nota o comentario que se quiera agregar, opcional.


## Clasificación técnica
![Clasificación técnica](images/clasificacion_tecnica.png)

- **Código:** Clave con el que será identificado.
- **Descripción:** Nombre de la Clasificación técnica. Esta es una subdivisión de la clasificación según uso.
- **Comentario:** Nota o comentario que se quiera agregar, opcional.
- **Clasificación Según Uso:** Es la clasificación según uso a la que pertenece.


## Significado de campos
Se utiliza para identificar de manera estándar los datos provenientes de los orígenes. Por ejemplo en una base de datos el campo puede llamarse **fecha_nacimiento**, en otra puede llamarse **fecha_nac** y en ambos casos se refiere a lo mismo.

![Significado campo](images/significado_campo.png)

- **Código:** Identificador del significado.
- **Descripción:** Nombre del significado
- **Utilizado para costeo:** Marque esta casilla, si el significado será exclusivo para formularios de costeo.
- **Describirá campos de catálogo:** Aquellos campos de tablas catálogos como, llave primaria, llave foránea, entre otros.
- **Catálogo asociado:** Asociar un catálogo a un significado permitirá cargar otros datos por ejemplo que el campo sea una llave foránea y los demás datos se encuentren en una tabla catálogo. Ej.: código de municipio, y en la tabla catálogo se encontrará el nombre del municipio.
- **Tipos de gráficos permitidos:** Se debe elegir los tipos de gráficos que se pueden usar sobre este campo

## Colores - Alertas
![Significado campo](images/colores_alertas.png)
- **Código:** Para crear colores y usarlos posteriormente en la definición de rangos de alertas, aquí ingresará un código del color en formato usado en HTML.
- **Color:** Nombre que se mostrará para el color


##Formato
Utilizado para definir los formatos de los campos al construir los formularios dinámicos

![Formato](images/formato.png)

- **Código:**: Identificador del formato
- **Descripción:** Nombre descriptivo del formato
- **Formato:** Código que utiliza la librería jqxGrid, que dibuja el formulario.
- **Tipo de dato:** Tipo de dato al que se le aplicará el formato

## Tipo de control
Se usa en la definición de los formularios dinámicos, se definen los tipos de controles que se podrán utilizar en los elementos del formulario.

![Tipo control](images/tipo_control.png)

- **Código:** Clave que identifica el tipo de control, debe coincidir con los utilizados por la librería jqxGrid.
- **Descripción:** Nombre que será mostrado

## Tipo Dato
Los tipos de datos utilizados al construir formularios, se pueden utilizar los siguientes tipos
| Código | Descripcion |
|--------|--------|
| bool   |  Falso/Verdadero      |
| date   |  Fecha      |
| float  |  Número flotante      |
| int    |   Entero     |
| string |  Cadena de texto      |


## Alineación
Para definir la alineación de un texto dentro de la celda, los valores posibles son:
| Código | Descripcion |
|--------|--------|
| center   |  Centrado     |
| left   |  Izquierda      |
| right  |  Derecha      |




