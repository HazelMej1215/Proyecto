package com.example.streamingapp.network

import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

data class LoginRequest(val correo: String, val password: String)
data class User(val id: Int, val nombre: String, val correo: String)
data class LoginResponse(val ok: Boolean, val msg: String, val user: User?)

data class Pelicula(
    val id: Int,
    val nombre: String,
    val genero: String,
    val descripcion: String,
    val ruta_imagen: String,
    val url_trailer: String
)
data class PeliculasResponse(val ok: Boolean, val peliculas: List<Pelicula>)

interface ApiService {
    @POST("login.php")
    suspend fun login(@Body body: LoginRequest): LoginResponse

    @GET("peliculas.php")
    suspend fun peliculas(): PeliculasResponse
}