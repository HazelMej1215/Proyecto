package com.example.streamingapp.network

import com.google.gson.annotations.SerializedName

data class Pelicula(
    val id: Int,
    val nombre: String?,
    val genero: String?,
    val descripcion: String?,

    @SerializedName("ruta_imagen")
    val rutaImagen: String?,

    @SerializedName("url_trailer")
    val urlTrailer: String?
)