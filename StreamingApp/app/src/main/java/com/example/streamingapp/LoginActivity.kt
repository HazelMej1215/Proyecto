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

class LoginActivity : AppCompatActivity() {

    // ✅ CELULAR (tu IP)
    private val registroUrl = "http://192.168.100.22/Proyecto/cliente_registro_solo.php"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        val etCorreo = findViewById<EditText>(R.id.etCorreo)
        val etPass = findViewById<EditText>(R.id.etPass)
        val btnLogin = findViewById<Button>(R.id.btnLogin)
        val tvRegistro = findViewById<TextView>(R.id.tvRegistro)

        tvRegistro.setOnClickListener {
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(registroUrl)))
        }

        btnLogin.setOnClickListener {
            val correo = etCorreo.text.toString().trim()
            val pass = etPass.text.toString().trim()

            lifecycleScope.launch {
                try {
                    val res = RetrofitClient.api.login(LoginRequest(correo, pass))
                    if (res.ok) {
                        startActivity(Intent(this@LoginActivity, MainActivity::class.java))
                        finish()
                    } else {
                        Toast.makeText(this@LoginActivity, res.msg ?: "Credenciales incorrectas", Toast.LENGTH_LONG).show()
                    }
                } catch (e: Exception) {
                    Toast.makeText(this@LoginActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
                }
            }
        }
    }
}