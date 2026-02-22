package com.example.streamingapp

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.streamingapp.network.LoginRequest
import com.example.streamingapp.network.RetrofitClient
import kotlinx.coroutines.launch
import retrofit2.HttpException

class LoginActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        val etCorreo = findViewById<EditText>(R.id.etCorreo)
        val etPass = findViewById<EditText>(R.id.etPass)
        val btnLogin = findViewById<Button>(R.id.btnLogin)
        val tvRegistro = findViewById<TextView>(R.id.tvRegistro)

        val registroUrl = AppConfig.BASE_WEB + "cliente_registro_solo.php"


        tvRegistro.setOnClickListener {
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(registroUrl)))
        }

        btnLogin.setOnClickListener {
            val correo = etCorreo.text.toString().trim()
            val pass = etPass.text.toString().trim()

            if (correo.isEmpty() || pass.isEmpty()) {
                Toast.makeText(this, "Completa correo y contraseña", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            lifecycleScope.launch {
                try {
                    val resp = RetrofitClient.api.login(LoginRequest(correo, pass))

                    if (resp.ok) {
                        Toast.makeText(
                            this@LoginActivity,
                            "Bienvenido ${resp.user?.nombre ?: ""}",
                            Toast.LENGTH_SHORT
                        ).show()

                        startActivity(Intent(this@LoginActivity, MainActivity::class.java))
                        finish()
                    } else {
                        Toast.makeText(this@LoginActivity, resp.msg ?: "Error", Toast.LENGTH_SHORT).show()
                    }

                } catch (e: HttpException) {
                    Toast.makeText(this@LoginActivity, "HTTP ${e.code()}", Toast.LENGTH_SHORT).show()
                } catch (e: Exception) {
                    Toast.makeText(this@LoginActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }
}