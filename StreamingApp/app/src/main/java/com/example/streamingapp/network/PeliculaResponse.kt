package com.example.streamingapp.network

data class PeliculasResponse(
    val ok: Boolean,
    val peliculas: List<Pelicula>
)