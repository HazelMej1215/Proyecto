package com.example.streamingapp.network

data class LoginResponse(
    val ok: Boolean,
    val msg: String? = null,
    val user: UserDto? = null
)

data class UserDto(
    val id: Int,
    val nombre: String,
    val correo: String
)