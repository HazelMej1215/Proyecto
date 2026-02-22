package com.example.streamingapp.network

import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.GET

interface ApiService {

    @POST("login.php")
    suspend fun login(@Body req: LoginRequest): LoginResponse

    @GET("peliculas_list.php")
    suspend fun getPeliculas(): PeliculasResponse
}