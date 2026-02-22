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
import retrofit2.HttpException

class MainActivity : AppCompatActivity() {

    private lateinit var recycler: RecyclerView
    private val adapter = PeliculasAdapter()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        recycler = findViewById(R.id.recyclerPeliculas)
        recycler.layoutManager = LinearLayoutManager(this)
        recycler.adapter = adapter

        cargarPeliculas()
    }

    private fun cargarPeliculas() {
        lifecycleScope.launch {
            try {
                val resp = RetrofitClient.api.getPeliculas()

                if (resp.ok) {
                    adapter.setData(resp.peliculas)
                } else {
                    Toast.makeText(
                        this@MainActivity,
                        "No se pudieron cargar las películas",
                        Toast.LENGTH_SHORT
                    ).show()
                }

            } catch (e: HttpException) {
                Toast.makeText(
                    this@MainActivity,
                    "Error HTTP ${e.code()}",
                    Toast.LENGTH_SHORT
                ).show()

            } catch (e: Exception) {
                Toast.makeText(
                    this@MainActivity,
                    "Error: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }
}