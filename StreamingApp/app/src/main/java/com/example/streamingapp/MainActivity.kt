package com.example.streamingapp

import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.streamingapp.network.RetrofitClient
import com.example.streamingapp.ui.PeliculasAdapter
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    companion object {
        // ✅ CELULAR (tu IP)
        private const val BASE_WEB = "http://192.168.100.22/Proyecto/"
        // ✅ EMULADOR (si lo usas)
        // private const val BASE_WEB = "http://10.0.2.2/Proyecto/"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        val rv = findViewById<RecyclerView>(R.id.rvPeliculas)
        rv.layoutManager = LinearLayoutManager(this)

        val adapter = PeliculasAdapter(BASE_WEB, mutableListOf())
        rv.adapter = adapter

        lifecycleScope.launch {
            try {
                val res = RetrofitClient.api.peliculas()
                if (!res.ok) {
                    Toast.makeText(this@MainActivity, "No se pudieron cargar", Toast.LENGTH_LONG).show()
                    return@launch
                }
                adapter.setItems(res.peliculas)
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
            }
        }
    }
}